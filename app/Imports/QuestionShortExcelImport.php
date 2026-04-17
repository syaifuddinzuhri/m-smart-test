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

class QuestionShortExcelImport implements ToCollection
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
        $dataRows = $rows->slice(7);

        $chunks = $dataRows->chunk(1);

        $preparedData = [];
        $isSequenceBroken = false;

        foreach ($chunks as $index => $chunk) {
            $nomorSoal = $index + 1;
            $excelRow = ($index * 2) + 8;

            $row = $chunk->first();
            $questionText = $row[1] ?? null;
            $correctAnswer = $row[2] ?? null;

            if (empty(trim($questionText)) && empty(trim($correctAnswer))) {
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

            $error = null;
            if (empty(trim($questionText))) {
                $error = "Teks soal tidak boleh kosong.";
            } elseif (empty(trim($correctAnswer))) {
                $error = "Kunci jawaban wajib diisi.";
            }

            $foundFile = $this->checkAttachment($nomorSoal);
            if ($foundFile && $foundFile['size'] > (20 * 1024 * 1024)) {
                $error = "File {$foundFile['name']} melebihi batas maksimal 20MB.";
            }

            if ($error) {
                $this->addError($excelRow, $nomorSoal, $questionText, $error);
            }

            $preparedData[] = [
                'excel_row' => $excelRow,
                'no' => $nomorSoal,
                'text' => $questionText,
                'correct_answer' => $correctAnswer,
                'attachment' => $foundFile
            ];
        }

        if (!empty($this->importErrors)) {
            throw new Exception("Validasi Gagal");
        }

        if (empty($preparedData)) {
            throw new Exception("Gagal import, data tidak ditemukan dalam file Excel.");
        }

        foreach ($preparedData as $item) {
            try {
                $question = Question::create([
                    'subject_id' => $this->subjectId,
                    'question_category_id' => $this->categoryId,
                    'question_text' => $item['text'],
                    'question_type' => QuestionType::SHORT_ANSWER->value,
                    'correct_answer_text' => $item['correct_answer'],
                ]);

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
