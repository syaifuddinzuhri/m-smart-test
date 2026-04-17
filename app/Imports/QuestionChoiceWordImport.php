<?php

namespace App\Imports;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuestionOption;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QuestionChoiceWordImport
{
    protected $subjectId;
    protected $categoryId;
    protected $basePath;
    protected $fileMap = [];
    protected $tempMediaPath;

    public array $importErrors = [];

    public function __construct($subjectId, $categoryId, $basePath = null)
    {
        $this->subjectId = $subjectId;
        $this->categoryId = $categoryId;
        $this->basePath = $basePath;
        $this->tempMediaPath = storage_path('app/temp_media_' . Str::random(10));
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

    /**
     * Pendeteksian Jalur Pandoc yang Sangat Kuat (Multi-OS)
     */
    private function getPandocPath()
    {
        // 1. Cek Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $winPaths = [
                'pandoc.exe',
                'C:\Program Files\Pandoc\pandoc.exe',
                'C:\Program Files (x86)\Pandoc\pandoc.exe'
            ];
            foreach ($winPaths as $path) {
                $check = (str_contains($path, ':')) ? file_exists($path) : shell_exec("where pandoc");
                if ($check)
                    return str_contains($path, ' ') ? '"' . $path . '"' : $path;
            }
        }

        // 2. Cek macOS (Darwin) & Linux
        $unixPaths = [
            '/usr/local/bin/pandoc',   // Intel Mac / Linux
            '/opt/homebrew/bin/pandoc', // Apple Silicon (M1/M2/M3)
            '/usr/bin/pandoc',          // Ubuntu/Debian Standard
            '/bin/pandoc'
        ];

        foreach ($unixPaths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        // 3. Fallback: Cek via 'which' di Unix
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $path = trim(shell_exec('which pandoc'));
            if (!empty($path))
                return $path;
        }

        return 'pandoc'; // Berharap ada di system PATH
    }

    public function convertDocxToMarkdown($filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception("File sumber Docx tidak ditemukan. Silahkan refresh halaman terlebih dahulu!");
        }

        $pandoc = $this->getPandocPath();

        $tempFile = storage_path('app/temp_' . Str::random(10) . '.html');

        $command = sprintf(
            '%s %s -f docx -t html --standalone --extract-media=%s -o %s 2>&1',
            $pandoc,
            escapeshellarg($filePath),
            escapeshellarg($this->tempMediaPath),
            escapeshellarg($tempFile)
        );

        // $command = sprintf(
        //     '%s %s -f docx -t html --standalone -o %s 2>&1',
        //     $pandoc,
        //     escapeshellarg($filePath),
        //     escapeshellarg($tempFile)
        // );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $errorMsg = implode(" ", $output);
            throw new Exception("Pandoc Error ({$returnVar}): {$errorMsg}. Path: {$pandoc}");
        }

        if (!file_exists($tempFile)) {
            throw new Exception("Gagal membuat file temporary markdown.");
        }

        $content = file_get_contents($tempFile);
        @unlink($tempFile); // Gunakan @ untuk suppress error jika file terkunci

        return $content;
    }

    private function parseMarkdownTable($markdown)
    {
        $tableData = [];
        $markdown = str_replace("\r\n", "\n", $markdown); // Normalisasi baris baru

        // --- MODE 1: HTML TABLE (Sering muncul di Ubuntu/Mac untuk sel kompleks) ---
        if (preg_match('/<table/i', $markdown)) {
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);

            // Konversi ke HTML-ENTITIES agar karakter UTF-8 (Arab/Simbol) tidak hilang
            $content = mb_convert_encoding($markdown, 'HTML-ENTITIES', 'UTF-8');
            $dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();

            $xpath = new \DOMXPath($dom);
            $mainTable = $xpath->query('//table')->item(0);

            if ($mainTable) {
                // Ambil baris dari tbody jika ada, jika tidak langsung dari table
                $rows = $xpath->query('.//tr', $mainTable);

                foreach ($rows as $row) {
                    $cells = $xpath->query('./td | ./th', $row);
                    $rowData = [];
                    foreach ($cells as $cell) {
                        $innerHtml = '';
                        foreach ($cell->childNodes as $child) {
                            $innerHtml .= $dom->saveHTML($child);
                        }
                        $rowData[] = $this->finalizeHtml($innerHtml);
                    }
                    if (!empty($rowData))
                        $tableData[] = $rowData;
                }
            }

            if (!empty($tableData))
                return $tableData;
        }

        // --- MODE 2: PIPE TABLE (| Soal | Jawaban |) ---
        $lines = explode("\n", $markdown);
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_contains($line, '|')) {
                $columns = array_map('trim', explode('|', trim($line, '|')));

                // Skip baris separator seperti |---|---|
                if (isset($columns[0]) && preg_match('/^[:\s-]*$/', $columns[0]))
                    continue;

                if (count($columns) >= 3)
                    $tableData[] = $columns;
            }
        }

        // --- MODE 3: GRID TABLE (+---+---+) ---
        // Jika Mode 2 gagal (biasanya Pandoc versi lama di Ubuntu), deteksi tanda '+'
        if (empty($tableData)) {
            foreach ($lines as $line) {
                if (str_contains($line, '+--') || trim($line) === '')
                    continue;
                if (str_contains($line, '|')) {
                    $columns = array_map('trim', explode('|', trim($line, '|')));
                    if (count($columns) >= 3)
                        $tableData[] = $columns;
                }
            }
        }

        return $tableData;
    }

    private function finalizeHtml($html)
    {
        if (empty($html))
            return "";

        $html = html_entity_decode($html);

        $html = preg_replace('/<table[^>]*>.*?<\/table>/is', '', $html);

        $allowedTags = '<span><strong><em><u><s><ul><ol><li><p><br><i><b><mark><sub><sup><math><img><div>';
        $cleaned = strip_tags($html, $allowedTags);

        // Bersihkan LaTeX
        $cleaned = $this->cleanLatex($cleaned);

        return trim($cleaned);
    }

    private function processHtmlImages($html, $questionId)
    {
        if (empty($html) || stripos($html, '<img') === false)
            return $html;

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $images = $dom->getElementsByTagName('img');
        foreach ($images as $img) {
            $src = $img->getAttribute('src');

            // LOGIKA PENENTUAN PATH FISIK FILE
            $sourcePath = null;

            // Cek 1: Jika src sudah merupakan path absolut yang valid
            if (file_exists($src)) {
                $sourcePath = $src;
            }
            // Cek 2: Jika src adalah path absolut tapi kurang "/" di depan (kasus MacOS/Linux tertentu)
            elseif (file_exists('/' . $src)) {
                $sourcePath = '/' . $src;
            }
            // Cek 3: Jika src adalah path relatif (misal: "media/image1.png")
            else {
                $cleanSrc = ltrim($src, './');
                $testPath = $this->tempMediaPath . DIRECTORY_SEPARATOR . $cleanSrc;
                if (file_exists($testPath)) {
                    $sourcePath = $testPath;
                }
            }

            // Jika file ditemukan, proses pemindahan ke Storage Laravel
            if ($sourcePath && file_exists($sourcePath)) {
                $extension = pathinfo($sourcePath, PATHINFO_EXTENSION); // Benar
                $filename = "img_" . Str::random(10) . "." . $extension;

                $storageFolder = "questions/{$questionId}";
                $storagePath = "{$storageFolder}/{$filename}";

                // Simpan ke storage permanen (public)
                Storage::disk('public')->put($storagePath, file_get_contents($sourcePath));

                // Update SRC menjadi URL publik (misal: /storage/questions/1/img_abc.png)
                $img->setAttribute('src', Storage::url($storagePath));

                // Tambahkan style agar rapi
                $img->setAttribute('style', 'max-width: 50%; height: auto;');
                $img->setAttribute('class', 'rounded-lg shadow-sm my-2');
            } else {
                // Jika file tetap tidak ditemukan, hapus tag img agar tidak muncul icon gambar pecah
                // atau biarkan saja untuk debugging.
                // $img->parentNode->removeChild($img);
            }
        }

        $container = $dom->getElementsByTagName('div')->item(0);
        $result = '';
        if ($container) {
            foreach ($container->childNodes as $node) {
                $result .= $dom->saveHTML($node);
            }
        }

        return $result;
    }

    private function cleanLatex($text)
    {
        // Ubah \( ... \) menjadi $ ... $ dan \[ ... \] menjadi $$ ... $$
        $text = preg_replace('/\\\\\((.*?)\\\\\)/s', '$$1$', $text);
        $text = preg_replace('/\\\\\[(.*?)\\\\\d*\]/s', '$$$$1$$', $text);
        return $text;
    }

    public function import($filePath)
    {
        // STRATEGI BARU:
        // 1. Kita ambil data teks dari Pandoc (untuk mendapatkan LaTeX)
        // 2. Kita tetap pakai PHPWord untuk mendeteksi struktur tabel jika perlu,
        // TAPI karena Pandoc sudah menghasilkan tabel Markdown, kita bisa parsing langsung dari Markdown.

        $markdownContent = $this->convertDocxToMarkdown($filePath);

        // Parsing tabel Markdown ke Array
        $rows = $this->parseMarkdownTable($markdownContent);

        if (empty($rows))
            throw new Exception("Tabel data tidak ditemukan atau format salah.");

        $dataRows = array_slice($rows, 1);
        $chunks = array_chunk($dataRows, 5);

        $maxOptionsFound = 0;
        $preparedData = [];
        $isSequenceBroken = false;

        // --- STAGE 1: VALIDASI & PREPARASI ---
        foreach ($chunks as $index => $chunk) {
            $visualRow = ($index * 5) + 2;
            $nomorSoal = $index + 1;

            // Kolom 1 di Markdown biasanya Soal
            $questionText = trim($chunk[0][1] ?? '');
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

            $optionsData = [];
            $correctCount = 0;

            foreach ($chunk as $row) {
                $optionText = trim($row[2] ?? ''); // Kolom Opsi
                $isCorrect = trim($row[3] ?? '') === '1'; // Kolom Kunci

                if (!empty($optionText)) {
                    $optionsData[] = ['text' => $optionText, 'is_correct' => $isCorrect];
                    if ($isCorrect)
                        $correctCount++;
                }
            }

            // Update jumlah opsi maksimal untuk validasi selaras
            $maxOptionsFound = max($maxOptionsFound, count($optionsData));

            // Cek File Multimedia & Ukurannya
            $foundFile = $this->checkAttachment($nomorSoal);

            $error = null;
            if ($correctCount === 0) {
                $error = "Belum ada kunci jawaban (angka 1).";
            } elseif ($foundFile && $foundFile['size'] > (3 * 1024 * 1024)) {
                $error = "File {$foundFile['name']} melebihi batas maksimal 3MB.";
            }

            if ($error) {
                $this->addError($visualRow, $nomorSoal, $questionText, $error);
            }

            $preparedData[] = [
                'visual_row' => $visualRow,
                'no' => $nomorSoal,
                'text' => $questionText,
                'options' => $optionsData,
                'correct_count' => $correctCount,
                'attachment' => $foundFile
            ];
        }

        // Validasi keselarasan jumlah opsi
        $requiredOptions = max(3, min(5, $maxOptionsFound));
        foreach ($preparedData as $key => $item) {
            if (count($item['options']) !== $requiredOptions) {
                $this->addError($item['visual_row'], $item['no'], $item['text'], "Jumlah opsi wajib {$requiredOptions}.");
            }
        }

        // Jika ada error di tahap validasi, hentikan sebelum masuk DB
        if (!empty($this->importErrors)) {
            throw new Exception("Validasi Gagal");
        }

        if (empty($preparedData)) {
            throw new Exception("Gagal import data tidak terbaca");
        }

        // --- STAGE 2: PROSES INSERT ---
        foreach ($preparedData as $item) {
            try {
                $type = ($item['correct_count'] > 1)
                    ? QuestionType::MULTIPLE_CHOICE->value
                    : QuestionType::SINGLE_CHOICE->value;

                $question = Question::create([
                    'subject_id' => $this->subjectId,
                    'question_category_id' => $this->categoryId,
                    'question_text' => $item['text'],
                    'question_type' => $type,
                ]);

                // PROSES GAMBAR DI DALAM SOAL & OPSI
                $finalText = $this->processHtmlImages($item['text'], $question->id);
                $question->update(['question_text' => $finalText]);

                // Simpan Opsi
                foreach ($item['options'] as $idx => $opt) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'text' => $opt['text'],
                        'is_correct' => $opt['is_correct'],
                        'label' => range('A', 'E')[$idx],
                        'order' => $idx,
                    ]);
                }

                // Simpan Attachment jika ada
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
                throw $e; // Trigger rollback
            } finally {
                if (File::exists($this->tempMediaPath)) {
                    File::deleteDirectory($this->tempMediaPath);
                }
            }
        }
    }

    private function checkAttachment($nomorSoal)
    {
        if (!$this->basePath)
            return null;

        if (empty($this->fileMap))
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
