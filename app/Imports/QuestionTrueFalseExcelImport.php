<?php

namespace App\Imports;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuestionOption;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class QuestionTrueFalseExcelImport implements ToCollection
{
    protected $subjectId;
    protected $categoryId;
    protected $basePath;
    protected $fileMap = [];

    public array $importErrors = [];

    public function __construct($subjectId, $categoryId, $basePath = null)
    {
        $this->subjectId = $subjectId;
        $this->categoryId = $categoryId;
        $this->basePath = $basePath;
        $this->indexFiles();
    }

    private function indexFiles()
    {
        if (!$this->basePath || !is_dir($this->basePath))
            return;

        $files = File::allFiles($this->basePath);
        foreach ($files as $file) {
            $this->fileMap[strtolower($file->getFilename())] = $file->getRealPath();
        }
    }

    public function collection(Collection $rows)
    {
        // Data dimulai dari baris ke-8 (index 7)
        $dataRows = $rows->slice(7);

        // CHUNK 2: Karena setiap soal Benar/Salah hanya punya 2 baris opsi
        $chunks = $dataRows->chunk(2);

        $preparedData = [];
        $isSequenceBroken = false;

        // --- STAGE 1: SCANNING & VALIDASI ---
        foreach ($chunks as $index => $chunk) {
            $nomorSoal = $index + 1;
            $excelRow = ($index * 2) + 8; // Row 8, 10, 12, dst

            $firstRow = $chunk->first();
            // Soal berada di baris pertama blok (Kolom B / index 1)
            $questionText = $firstRow[1] ?? null;

            if (empty(trim($questionText))) {
                $isSequenceBroken = true;
                continue;
            }

            if ($isSequenceBroken && !empty($questionText)) {
                $this->addError(
                    $excelRow,
                    $nomorSoal,
                    $questionText,
                    "Nomor soal melompat. Harap pastikan baris Excel tidak ada yang kosong di tengah data."
                );
            }

            $optionsData = [];
            $correctCount = 0;

            foreach ($chunk as $row) {
                $optionText = trim($row[2] ?? ''); // Kolom C (Jawaban)
                $isCorrect = (int) ($row[3] ?? 0) === 1; // Kolom D (Kunci)

                if ($optionText !== '') {
                    $optionsData[] = [
                        'text' => $optionText,
                        'is_correct' => $isCorrect,
                    ];
                    if ($isCorrect)
                        $correctCount++;
                }
            }

            // Cek Attachment (soal-1.jpg, dst)
            $foundFile = $this->checkAttachment($nomorSoal);

            // VALIDASI KHUSUS T/F
            $error = null;
            if (count($optionsData) < 2) {
                $error = "Opsi Benar/Salah tidak lengkap.";
            } elseif ($correctCount !== 1) {
                $error = "Kunci jawaban harus tepat satu (angka 1).";
            } elseif ($foundFile && $foundFile['size'] > (20 * 1024 * 1024)) {
                $error = "File {$foundFile['name']} melebihi batas maksimal 20MB.";
            }

            if ($error) {
                $this->addError($excelRow, $nomorSoal, $questionText, $error);
            }

            $preparedData[] = [
                'excel_row' => $excelRow,
                'no' => $nomorSoal,
                'text' => $questionText,
                'options' => $optionsData,
                'correct_count' => $correctCount,
                'attachment' => $foundFile
            ];
        }

        // Jika ada error di tahap validasi, hentikan sebelum masuk DB
        if (!empty($this->importErrors)) {
            throw new Exception("Validasi Gagal");
        }

        if (empty($preparedData)) {
            throw new Exception("Gagal import, data tidak ditemukan dalam file Excel.");
        }

        // --- STAGE 2: PROSES INSERT (ATOMIC) ---
        foreach ($preparedData as $item) {
            try {
                // Tipe soal otomatis TRUE_FALSE
                $question = Question::create([
                    'subject_id' => $this->subjectId,
                    'question_category_id' => $this->categoryId,
                    'question_text' => $item['text'],
                    'question_type' => QuestionType::TRUE_FALSE->value,
                ]);

                $labels = ['A', 'B'];
                foreach ($item['options'] as $idx => $opt) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'text' => $opt['text'],
                        'is_correct' => $opt['is_correct'],
                        'label' => $labels[$idx],
                        'order' => $idx,
                    ]);
                }

                if ($item['attachment']) {
                    $newPath = generateFilePath('questions', $question->id, 1, $item['attachment']['path']);
                    Storage::disk('public')->put($newPath, file_get_contents($item['attachment']['path']));

                    $question->attachments()->create([
                        'id' => Str::uuid(),
                        'file_path' => $newPath,
                        'type' => $item['attachment']['type'],
                    ]);
                }
            } catch (Exception $e) {
                throw new Exception("Baris Excel {$item['excel_row']}: Database Error - " . $e->getMessage());
            }
        }
    }

    private function checkAttachment($nomorSoal)
    {
        if (!$this->basePath || empty($this->fileMap))
            return null;

        $extensions = [
            'image' => ['png', 'jpg', 'jpeg', 'gif'],
            'audio' => ['mp3', 'wav'],
            'video' => ['mp4', 'webm'],
        ];

        foreach ($extensions as $type => $exts) {
            foreach ($exts as $ext) {
                $searchName = strtolower("soal-{$nomorSoal}.{$ext}");
                if (isset($this->fileMap[$searchName])) {
                    $fullPath = $this->fileMap[$searchName];
                    return [
                        'path' => $fullPath,
                        'name' => basename($fullPath),
                        'size' => filesize($fullPath),
                        'type' => $type
                    ];
                }
            }
        }
        return null;
    }

    private function addError($row, $no, $text, $reason)
    {
        $this->importErrors[] = [
            'row' => $row,
            'no' => $no,
            'question' => Str::limit($text, 50),
            'reason' => $reason
        ];
    }
}
