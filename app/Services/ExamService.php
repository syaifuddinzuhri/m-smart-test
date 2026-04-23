<?php

namespace App\Services;

use App\Enums\ExamSessionStatus;
use App\Models\ExamAnswer;
use App\Models\ExamSession;
use App\Enums\QuestionType;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;

class ExamService
{
    // INITIAL SESSION
    public function initializeExamSession(User $user, Exam $exam)
    {
        $exam->loadMissing(['questions']);

        $userId = $user->id;
        $examId = $exam->id;

        $session = ExamSession::firstOrNew(
            ['exam_id' => $examId, 'user_id' => $userId]
        );

        if ($session->exists && $session->status === ExamSessionStatus::COMPLETED) {
            throw new Exception("Anda sudah menyelesaikan ujian ini.");
        }

        if ($session->violation_count >= 5) {
            $session->update(['status' => ExamSessionStatus::PAUSE]);
            throw new Exception("Anda terlalu sering keluar atau melanggar ketentuan ujain. Hubungi pengawas agar ditindak lanjut");
        }

        $plainToken = Str::random(64);
        $tokenHash = hash('sha256', $plainToken);

        $updateData = [
            'token' => $tokenHash,
            'system_id' => generate_exam_system_id($tokenHash),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'device_type' => getDeviceInfo(),
            'status' => ExamSessionStatus::ONGOING->value,
        ];

        if (!$session->started_at) {
            $uniqueQuestionSeed = crc32($userId . $examId . 'question' . now()->timestamp);
            $uniqueOptionSeed = crc32($userId . $examId . 'option' . now()->timestamp);

            $sessionExtensionLog = $session->extension_log ?? [];

            if (count($sessionExtensionLog) > 0) {
                $additional = collect($sessionExtensionLog)->sum('minutes');
            } else {
                $additional = collect($exam->extension_log ?? [])->sum('minutes');
            }

            /**
             * LOGIKA INISIALISASI PINALTI (HUTANG POIN)
             * Menghitung total pinalti awal berdasarkan jumlah soal
             */
            $initialPg = 0;
            $initialShort = 0;
            $initialEssay = 0;

            foreach ($exam->questions as $q) {
                if ($q->isPg()) {
                    $initialPg -= (float) $exam->point_pg_null;
                } elseif ($q->isShortAnswer()) {
                    $initialShort -= (float) $exam->point_short_answer_null;
                } elseif ($q->isEssay()) {
                    $initialEssay -= (float) $exam->point_essay_null;
                }
            }

            $updateData = array_merge($updateData, [
                'started_at' => now(),
                'expires_at' => now()->addMinutes($exam->duration + $additional),
                'question_seed' => $uniqueQuestionSeed,
                'option_seed' => $uniqueOptionSeed,
                'score_pg' => $initialPg,
                'score_short_answer' => $initialShort,
                'score_essay' => $initialEssay,
                'total_score' => 0,
            ]);

        }

        $session->fill($updateData);
        $session->save();

        return $plainToken;
    }

    /**
     * Hitung total poin maksimal yang bisa didapat dari sebuah ujian.
     * Digunakan sebagai pembagi untuk normalisasi skor (misal ke skala 100).
     */
    public function getMaxPossibleRawScore(Exam $exam): float
    {
        $exam->loadMissing(['questions']);

        // Cache hasil perhitungan jika perlu untuk performa
        $questions = $exam->questions;

        $total = 0;
        foreach ($questions as $q) {
            $total += match (true) {
                $q->isPg() => (float) $exam->point_pg,
                $q->isShortAnswer() => (float) $exam->point_short_answer,
                $q->isEssay() => (float) $exam->point_essay_max,
                default => 0
            };
        }

        return $total > 0 ? $total : 1; // Hindari pembagian dengan nol
    }

    /**
     * Hitung Skor mentah tanpa update ke database
     */
    public function calculateScore(ExamAnswer $answer): ?float
    {
        $answer->load([
            'question.options',
            'selectedOptions',
            'session.exam'
        ]);
        $question = $answer->question;
        $exam = $answer->session->exam;

        if ($question->question_type === QuestionType::ESSAY) {
            return (float) ($answer->score ?? 0);
        }

        $isCorrect = false;
        $penaltyScore = 0;
        $positiveScore = 0;

        if ($question->isPg()) {
            $positiveScore = $exam->point_pg;
            $penaltyScore = $exam->point_pg_wrong;
        } elseif ($question->isShortAnswer()) {
            $positiveScore = $exam->point_short_answer;
            $penaltyScore = $exam->point_short_answer_wrong;
        }

        switch ($question->question_type) {
            case QuestionType::SINGLE_CHOICE:
            case QuestionType::TRUE_FALSE:
                $correctOption = $question->options->where('is_correct', true)->first();
                $selectedOption = $answer->selectedOptions->first();
                $isCorrect = $selectedOption && $correctOption && ($selectedOption->id === $correctOption->id);
                break;

            case QuestionType::MULTIPLE_CHOICE:
                $correctOptionIds = $question->options->where('is_correct', true)->pluck('id')->sort()->values()->toArray();
                $selectedOptionIds = $answer->selectedOptions->pluck('id')->sort()->values()->toArray();
                $isCorrect = ($correctOptionIds === $selectedOptionIds);
                break;

            case QuestionType::SHORT_ANSWER:
                $studentText = trim(strtolower($answer->answer_text ?? ''));
                $keys = collect(explode('|', $question->correct_answer_text ?? ''))->map(fn($k) => trim(strtolower($k)))->filter();
                $isCorrect = $keys->contains($studentText);
                break;
        }

        if ($isCorrect) {
            return (float) $positiveScore;
        } else {
            return -(float) $penaltyScore;
        }
    }

    public function updateIncrementalScore(ExamSession $session, QuestionType $questionType, float $oldScore, float $newScore): void
    {
        $diff = $newScore - $oldScore;
        if ($diff == 0)
            return;

        $exam = $session->exam;
        $column = match ($questionType) {
            QuestionType::SINGLE_CHOICE, QuestionType::MULTIPLE_CHOICE, QuestionType::TRUE_FALSE => 'score_pg',
            QuestionType::SHORT_ANSWER => 'score_short_answer',
            QuestionType::ESSAY => 'score_essay',
            default => null
        };

        if ($column) {
            // 1. Update Sub-Skor di Database
            $session->increment($column, $diff);

            // Refresh data session agar mendapatkan nilai terbaru setelah increment
            $session->refresh();

            // 2. Hitung Ulang Total Score secara Utuh (Agar sinkron dengan Sync)
            $rawTotal = (float) $session->score_pg + (float) $session->score_short_answer + (float) $session->score_essay;

            $finalTotal = $this->calculateFinalScore($exam, $rawTotal);

            // 3. Update Total Score Hasil Kalkulasi Ulang
            $session->update([
                'total_score' => $finalTotal
            ]);
        }
    }

    public function manualVerify(ExamAnswer $answer, bool $isCorrect, ?float $essayScore = null): void
    {
        if ($answer->session->finalized_at) {
            throw new Exception("Hasil ujian ini sudah difinalisasi dan tidak dapat diubah lagi.");
        }

        $answer->load(['session.exam', 'question']);
        $exam = $answer->session->exam;
        $question = $answer->question;
        $oldScore = (float) $answer->score;

        $newScore = 0;

        // 1. LOGIKA KHUSUS ESSAY
        if ($question->isEssay()) {
            if ($essayScore !== null) {
                if ($essayScore > $exam->point_essay_max) {
                    throw new Exception("Skor gagal disimpan. Maksimal poin essay adalah {$exam->point_essay_max}");
                }
                if ($essayScore < 0) {
                    throw new Exception("Skor tidak boleh kurang dari 0.");
                }
                $newScore = $essayScore;
                $isCorrect = $newScore > 0;
            } else {
                // Jika admin hanya klik tombol Benar/Salah tanpa input angka
                $newScore = $isCorrect ? (float) $exam->point_essay_max : 0;
            }
        }

        // 2. LOGIKA UNTUK NON-ESSAY (Jika admin ingin override PG/Short Answer)
        else {
            if ($isCorrect) {
                $newScore = match (true) {
                    $question->isPg() => (float) $exam->point_pg,
                    $question->isShortAnswer() => (float) $exam->point_short_answer,
                    default => 0
                };
            } else {
                // PERBAIKAN: Gunakan NEGATIF jika salah (Sesuai sistem pinalti)
                $newScore = match (true) {
                    $question->isPg() => -(float) $exam->point_pg_wrong,
                    $question->isShortAnswer() => -(float) $exam->point_short_answer_wrong,
                    default => 0
                };
            }
        }

        // 3. SIMPAN PERUBAHAN KE JAWABAN
        $answer->update([
            'is_correct' => $isCorrect,
            'score' => $newScore,
        ]);

        // 4. SINKRONISASI TOTAL SKOR SESI
        // Kita gunakan incremental karena lebih efisien dan sudah mencakup hitung ulang total_score
        $this->updateIncrementalScore($answer->session, $answer->question->question_type, $oldScore, $newScore);
    }

    public function calculateFinalScore(Exam $exam, float $rawTotal): float
    {
        // PASTIKAN NAMA KOLOM BENAR: target_max_score
        $targetMaxScore = $exam->target_max_score;

        // Jika admin tidak menset target score (null atau 0), tampilkan poin mentah apa adanya
        if (is_null($targetMaxScore) || $targetMaxScore == 0) {
            return $rawTotal;
        }

        // Jika ada target (misal 100), lakukan normalisasi
        $cleanRawTotal = max(0, $rawTotal);
        $maxRaw = $this->getMaxPossibleRawScore($exam);

        // Rumus: (Poin didapat / Poin Maksimal) * Target (misal 100)
        return $maxRaw > 0 ? ($cleanRawTotal / $maxRaw) * $targetMaxScore : 0;
    }
    /**
     * Sinkronisasi total skor PG, Isian, Essay ke tabel ExamSession.
     * Termasuk menangani poin untuk soal yang tidak dijawab (null point).
     */
    public function syncSessionScores(ExamSession $session): void
    {
        $session->loadMissing([
            'exam.questions',
        ]);
        $exam = $session->exam;

        // 1. Ambil semua soal dalam ujian ini
        $questions = $exam->questions; // Pastikan relasi questions ada di Model Exam

        // 2. Ambil semua jawaban yang sudah masuk
        $answers = ExamAnswer::with(['selectedOptions'])->where('exam_session_id', $session->id)->get()->keyBy('question_id');

        $totalPg = 0;
        $totalShort = 0;
        $totalEssay = 0;

        foreach ($exam->questions as $q) {
            $answer = $answers->get($q->id);
            $hasContent = $answer && ($q->isPg()
                ? $answer->selectedOptions->count() > 0
                : trim(strip_tags($answer->answer_text ?? '')) !== '');

            if (!$hasContent) {
                // PINALTI KOSONG (NEGATIF)
                if ($q->isPg())
                    $totalPg -= (float) $exam->point_pg_null;
                elseif ($q->isShortAnswer())
                    $totalShort -= (float) $exam->point_short_answer_null;
                elseif ($q->isEssay())
                    $totalEssay -= (float) $exam->point_essay_null;
            } else {
                // --- ADA JAWABAN ---
                if ($q->isEssay()) {
                    $totalEssay += is_null($answer->is_correct) ? 0 : (float) $answer->score;
                }
                // PERBAIKAN DI SINI: Pisahkan PG dan Short Answer
                elseif ($q->isShortAnswer()) {
                    $totalShort += (float) ($answer->score);
                } elseif ($q->isPg()) {
                    $totalPg += (float) ($answer->score);
                }

                if ($answer->is_doubtful) {
                    $answer->update(['is_doubtful' => null]);
                }
            }
        }

        $rawTotal = $totalPg + $totalShort + $totalEssay;
        $finalTotal = $this->calculateFinalScore($exam, $rawTotal);

        $hasManualType = $questions->contains(fn($q) => $q->isShortAnswer() || $q->isEssay());

        $updateData = [
            'score_pg' => $totalPg,
            'score_short_answer' => $totalShort,
            'score_essay' => $totalEssay,
            'total_score' => max(0, $finalTotal),
        ];

        // Jika soal di ujian hanya PG saja (tidak mengandung Short Answer atau Essay)
        // Dan saat ini finalized_at masih kosong, maka otomatis diisi.
        if (!$hasManualType && is_null($session->finalized_at)) {
            $updateData['finalized_at'] = now();
        }

        $session->update($updateData);
    }

    public function getQuestions(Exam $exam, ExamSession $session, bool $isOrdered = true)
    {
        $results = [];

        // 1. Ambil Jawaban Siswa terlebih dahulu
        $answers = ExamAnswer::where('exam_session_id', $session->id)
            ->with('selectedOptions')
            ->get()
            ->keyBy('question_id');

        // 2. Tentukan Query Dasar
        $query = ExamQuestion::query()
            ->where('exam_id', $exam->id);

        if ($isOrdered) {
            // --- LOGIKA JIKA URUT/ACAK (Standard Ujian) ---
            $qSeed = match ((int) $exam->random_question_type) {
                1 => $session->question_seed,
                2 => crc32($exam->id),
                default => null,
            };

            $examQuestions = $query->with([
                'question.options' => function ($query) use ($exam, $session) {
                    if ($exam->random_option_type) {
                        $query->inRandomOrder($session->option_seed);
                    } else {
                        $query->orderBy('order', 'asc');
                    }
                }
            ])
                ->when(
                    $qSeed,
                    fn($q) => $q->inRandomOrder($qSeed),
                    fn($q) => $q->orderBy('order', 'asc')
                )
                ->get();
        } else {
            // --- LOGIKA JIKA TIDAK URUT (Grouping Berdasarkan Tipe) ---
            // Join ke tabel questions untuk mendapatkan question_type agar bisa diurutkan
            $examQuestions = $query->join('questions', 'exam_questions.question_id', '=', 'questions.id')
                ->select('exam_questions.*', 'questions.question_type')
                ->with(['question.options' => fn($q) => $q->orderBy('order', 'asc')])
                ->get()
                ->sort(function ($a, $b) {
                    // Tentukan hirarki prioritas
                    $priority = [
                        QuestionType::SINGLE_CHOICE->value => 1,
                        QuestionType::MULTIPLE_CHOICE->value => 1,
                        QuestionType::TRUE_FALSE->value => 1,
                        QuestionType::SHORT_ANSWER->value => 2,
                        QuestionType::ESSAY->value => 3,
                    ];

                    $prioA = $priority[$a->question_type->value] ?? 4;
                    $prioB = $priority[$b->question_type->value] ?? 4;

                    if ($prioA === $prioB) {
                        // Jika tipe sama, urutkan berdasarkan waktu buat atau ID agar konsisten
                        return $a->created_at <=> $b->created_at;
                    }

                    return $prioA <=> $prioB;
                });
        }

        foreach ($examQuestions as $index => $eq) {
            $question = $eq->question;
            $studentAnswer = $answers->get($question->id);

            // 1. Menggunakan match(true) dengan helper model
            $uiType = match (true) {
                $question->isSingleChoice() => 'Pilihan Ganda (Satu Jawaban)',
                $question->isMultipleChoice() => 'Pilihan Ganda (Multi Jawaban)',
                $question->isTrueFalse() => 'Pilihan Ganda (Benar Salah)',
                $question->isShortAnswer() => 'Isian Singkat',
                $question->isEssay() => 'Essay',
                default => '-'
            };

            $formattedOptions = [];
            // Tentukan apakah ini multiple choice murni untuk logic array jawaban
            $isMultipleChoice = $question->question_type === QuestionType::MULTIPLE_CHOICE;
            $examAnswerKey = $isMultipleChoice ? [] : null;

            $letters = range('a', 'z');

            // 2. Gunakan helper untuk menentukan apakah butuh mapping opsi
            if ($question->isPg()) {
                foreach ($question->options as $optIndex => $option) {
                    $char = $letters[$optIndex];
                    $formattedOptions[$char] = $option->text;

                    $isPicked = $studentAnswer && $studentAnswer->selectedOptions->contains('id', $option->id);

                    if ($isPicked) {
                        if ($isMultipleChoice) {
                            $examAnswerKey[] = $char;
                        } else {
                            $examAnswerKey = $char;
                        }
                    }
                }
            } else {
                // Untuk Essay atau Short Answer
                $examAnswerKey = $studentAnswer->answer_text ?? null;
            }

            $results[] = [
                'number' => $index + 1,
                'is_correct' => $studentAnswer?->is_correct ?? null,
                'has_answer' => $studentAnswer && (!empty($studentAnswer->answer_text) || $studentAnswer->selectedOptions()->exists()),
                'point_pg_null' => $exam->point_pg_null,
                'point_short_answer_null' => $exam->point_short_answer_null,
                'point_essay_null' => $exam->point_essay_null,
                'type_label' => $uiType,
                'type' => $question->question_type,
                'is_pg' => $question->isPg(),
                'is_essay' => $question->isEssay(),
                'is_single' => $question->isSingleChoice(),
                'is_multiple' => $question->isMultipleChoice(),
                'is_short' => $question->isShortAnswer(),
                'question' => $question->question_text,
                'options' => $formattedOptions,
                'answer' => $examAnswerKey,
                'score' => number_format($studentAnswer->score ?? 0, 2),
            ];
        }
        return $results;
    }
}
