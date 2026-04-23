<?php

namespace App\Exports;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

class QuestionShortWordTemplateExport
{
    public static function export()
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $section->addText("PETUNJUK PENGISIAN SOAL ISIAN SINGKAT", ['bold' => true, 'size' => 14]);
        $section->addListItem("Tulis teks SOAL pada kolom SOAL.");
        $section->addListItem("Tulis jawaban yang benar pada kolom KUNCI JAWABAN.");
        $section->addListItem("Gunakan tanda | sebagai pemisah jika terdapat lebih dari satu jawaban benar (Contoh: Soekarno|Ir. Soekarno).");
        $section->addListItem("Isian singkat biasanya berupa satu kata atau frase pendek.");
        $section->addListItem("Satu baris mewakili satu nomor soal.");
        $section->addTextBreak(1);

        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80,
        ];
        $firstRowStyle = ['bgColor' => '10B981'];

        $phpWord->addTableStyle('QuestionTable', $tableStyle);
        $table = $section->addTable('QuestionTable');

        $table->addRow();
        $table->addCell(800, $firstRowStyle)->addText("NO", ['bold' => true, 'color' => 'FFFFFF'], ['alignment' => Jc::CENTER]);
        $table->addCell(6000, $firstRowStyle)->addText("SOAL", ['bold' => true, 'color' => 'FFFFFF'], ['alignment' => Jc::CENTER]);
        $table->addCell(3500, $firstRowStyle)->addText("KUNCI JAWABAN", ['bold' => true, 'color' => 'FFFFFF'], ['alignment' => Jc::CENTER]);

        for ($i = 1; $i <= 20; $i++) {
            $table->addRow();
            $table->addCell(800)->addText($i, null, ['alignment' => Jc::CENTER]);
            $table->addCell(6000)->addText($i === 1 ? "Contoh: Siapakah presiden pertama Republik Indonesia?" : "");
            $table->addCell(3500)->addText($i === 1 ? "Ir. Soekarno|Soekarno" : "");
        }

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $fileName = 'template_soal_jawaban_singkat_' . now()->format('Ymd_His') . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $objWriter->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}
