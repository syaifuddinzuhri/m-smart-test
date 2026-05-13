<?php

namespace App\Imports;

use App\Enums\QuestionType;
use App\Models\Question;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QuestionEssayWordImport extends BaseQuestionWordImport
{
    public function import($filePath)
    {
        $markdownContent = $this->convertDocxToMarkdown($filePath);

        $rows = $this->parseMarkdownTable($markdownContent);

        if (empty($rows))
            throw new Exception("Tabel data tidak ditemukan atau format salah.");

        $dataRows = array_slice($rows, 1);

        $preparedData = [];
        $isSequenceBroken = false;

        foreach ($dataRows as $index => $row) {
            $visualRow = $index + 8;
            $nomorSoal = $index + 1;
            $questionText = trim($row[1] ?? '');

            if (empty($questionText)) {
                $isSequenceBroken = true;
                continue;
            }

            if ($isSequenceBroken && !empty($questionText)) {
                $this->addError(
                    $visualRow,
                    $nomorSoal,
                    $questionText,
                    "Nomor soal melompat. Nomor soal sebelumnya kosong, harap pastikan soal berurutan tanpa ada nomor yang dilewati."
                );
            }

            $error = null;

            if (empty($questionText)) {
                $error = "Teks soal tidak boleh kosong.";
            }

            $foundFile = $this->checkAttachment($nomorSoal);
            if ($foundFile && $foundFile['size'] > (3 * 1024 * 1024)) {
                $error = "File {$foundFile['name']} melebihi batas maksimal 3MB.";
            }

            if ($error) {
                $this->addError($visualRow, $nomorSoal, $questionText, $error);
            }

            $preparedData[] = [
                'visual_row' => $visualRow,
                'no' => $nomorSoal,
                'text' => $questionText,
                'attachment' => $foundFile
            ];
        }

        if (!empty($this->importErrors))
            throw new Exception("Validasi Gagal");

        if (empty($preparedData)) {
            throw new Exception("Gagal import, data tidak ditemukan dalam file Word.");
        }

        foreach ($preparedData as $item) {
            try {
                $question = Question::create([
                    'subject_id' => $this->subjectId,
                    'question_category_id' => $this->categoryId,
                    'question_text' => $item['text'],
                    'question_type' => QuestionType::ESSAY->value,
                ]);

                $finalText = $this->processHtmlImages($item['text'], $question->id);
                $question->update(['question_text' => $finalText]);

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
                $this->addError($item['visual_row'], $item['no'], $item['text'], "Database Error: " . $e->getMessage());
                throw $e;
            }
        }
        $this->cleanup();
    }
}
