<?php

namespace App\Filament\Student\Pages;

use App\Enums\ExamSessionStatus;
use App\Enums\QuestionType;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamClassroom;
use App\Models\ExamQuestion;
use App\Models\ExamSession;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class DetailResultTest extends Page
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'result-test/{record}';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.student.pages.detail-result-test';

    public ?Exam $exam = null;

    public ?ExamSession $session = null;
    public array $stats = [];
    public array $results = [];

    public function mount($record)
    {
        $user = Auth::user();
        $userId = $user->id;

        $this->exam = Exam::where('id', $record)
            ->with(['category', 'subject'])
            ->withCount('examQuestions')
            ->first();

        $this->session = ExamSession::where('exam_id', $record)
            ->where('user_id', $userId)
            ->where('status', ExamSessionStatus::COMPLETED)
            ->first();

        if (!$this->exam || !$this->session) {
            return redirect()->to(ResultTest::getUrl());
        }

        $questions = $this->exam->questions()->get();

        // Kelompokkan berdasarkan tipe
        $countPG = $questions->whereIn('question_type', [
            QuestionType::SINGLE_CHOICE,
            QuestionType::MULTIPLE_CHOICE,
            QuestionType::TRUE_FALSE
        ])->count();

        $countShortAnswer = $questions->where('question_type', QuestionType::SHORT_ANSWER)->count();
        $countEssay = $questions->where('question_type', QuestionType::ESSAY)->count();

        $passingGrade = ExamClassroom::where('exam_id', $this->exam->id)
            ->where('classroom_id', $user->student?->classroom_id)
            ->value('min_total_score');

        $answers = ExamAnswer::where('exam_session_id', $this->session->id)->get();
        $totalQuestions = $this->exam->exam_questions_count;
        $submittedAnswersCount = $answers->count();

        if ($this->session->started_at && $this->session->finished_at) {
            $actualDuration = (int) $this->session->started_at->diffInMinutes($this->session->finished_at);
        } else {
            $actualDuration = 0;
        }
        $this->stats = [
            'actual_duration' => $actualDuration,
            'total_questions' => $totalQuestions,
            'count_pg' => $countPG,
            'count_short' => $countShortAnswer,
            'count_essay' => $countEssay,
            'correct_answers' => $answers->where('is_correct', true)->count(),
            'wrong_answers' => $answers->where('is_correct', false)->count(),
            'pending_review' => $answers->whereNull('is_correct')->count(),
            'unanswered' => max(0, $totalQuestions - $submittedAnswersCount),
            'score' => number_format($this->session->total_score, 2),
            'classroom' => $user->student?->classroom?->name . ' - ' . $user->student?->classroom?->major?->name,
            'passing_grade' => $passingGrade,
            'is_passed' => is_null($passingGrade) || ($this->session->total_score >= $passingGrade),
        ];

        $this->getQuestions();

    }

    private function getQuestions()
    {
        $qSeed = match ((int) $this->exam->random_question_type) {
            1 => $this->session->question_seed,
            2 => crc32($this->exam->id),
            default => null,
        };

        $examQuestions = ExamQuestion::query()
            ->where('exam_id', $this->exam->id)
            ->with([
                'question.options' => function ($query) {
                    if ($this->exam->random_option_type) {
                        $query->inRandomOrder($this->session->option_seed);
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

        $answers = ExamAnswer::where('exam_session_id', $this->session->id)
            ->with('selectedOptions')
            ->get()
            ->keyBy('question_id');

        foreach ($examQuestions as $index => $eq) {
            $question = $eq->question;
            $studentAnswer = $answers->get($question->id);

            // 1. Menggunakan match(true) dengan helper model
            $uiType = match (true) {
                $question->isSingleChoice() => 'Pilihan Ganda (Satu Jawaban)',
                $question->isMultipleChoice() => 'Pilihan Ganda (Multi Jawaban)',
                $question->isTrueFalse() => 'Pilihan Ganda (Benar Salah)',
                $question->isShortAnswer() => 'Jawaban Singkat',
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

            $this->results[] = [
                'number' => $index + 1,
                'is_correct' => $studentAnswer->is_correct,
                'type_label' => $uiType,
                'type' => $question->question_type,
                'is_pg' => $question->isPg(),
                'is_essay' => $question->isEssay(),
                'is_multiple' => $question->isMultipleChoice(),
                'is_short' => $question->isShortAnswer(),
                'question' => $question->question_text,
                'options' => $formattedOptions,
                'answer' => $examAnswerKey,
                'score' => number_format($studentAnswer->score ?? 0, 2),
            ];
        }
    }

    public function getTitle(): string|Htmlable
    {
        return 'Detail Hasil Ujian - ' . $this->exam->title;
    }

    public function getHeading(): string|Htmlable
    {
        return new HtmlString('
        <div class="flex flex-col gap-1">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-widest leading-none">
                Detail Hasil Ujian
            </span>
            <span class="text-2xl font-extrabold text-gray-900 leading-tight">
                ' . e($this->exam->title) . '
            </span>
        </div>
    ');
    }
}
