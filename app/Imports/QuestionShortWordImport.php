<?php

namespace App\Imports;

use App\Enums\QuestionType;
use App\Models\Question;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QuestionShortWordImport extends BaseQuestionWordImport
{
    public function import($filePath)
    {
        $markdownContent = $this->convertDocxToMarkdown($filePath);

        $rows = $this->parseMarkdownTable($markdownContent);

        if (empty($rows))
            throw new Exception("Tabel data tidak ditemukan atau format salah.");

        $chunks = array_chunk(array_slice($rows, 1), 1);

        $preparedData = [];
        $isSequenceBroken = false;

        foreach ($chunks as $index => $chunk) {
            $visualRow = ($index * 1) + 8;
            $nomorSoal = $index + 1;
            $questionText = trim($chunk[0][1] ?? '');
            $correctAnswer = trim($chunk[0][2] ?? '');

            if (empty($questionText) && empty($correctAnswer)) {
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
            } elseif (empty($correctAnswer)) {
                $error = "Kunci jawaban wajib diisi.";
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
                'correct_answer' => $correctAnswer,
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
                    'question_type' => QuestionType::SHORT_ANSWER->value,
                    'correct_answer_text' => $item['correct_answer'],
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
