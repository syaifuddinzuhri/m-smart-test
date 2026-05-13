<?php

namespace App\Exports;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

class QuestionEssayWordTemplateExport
{
    public static function export()
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $section->addText("PETUNJUK PENGISIAN SOAL ESSAY / URAIAN", ['bold' => true, 'size' => 14]);
        $section->addListItem("Tulis teks SOAL pada kolom SOAL.");
        $section->addListItem("Soal essay akan dinilai secara manual oleh pengajar setelah siswa mengerjakan ujian.");
        $section->addListItem("Satu baris mewakili satu nomor soal.");
        $section->addTextBreak(1);

        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80,
        ];
        $firstRowStyle = ['bgColor' => '8B5CF6'];

        $phpWord->addTableStyle('QuestionTable', $tableStyle);
        $table = $section->addTable('QuestionTable');

        $table->addRow();
        $table->addCell(800, $firstRowStyle)->addText("NO", ['bold' => true, 'color' => 'FFFFFF'], ['alignment' => Jc::CENTER]);
        $table->addCell(9500, $firstRowStyle)->addText("SOAL", ['bold' => true, 'color' => 'FFFFFF'], ['alignment' => Jc::CENTER]);

        for ($i = 1; $i <= 20; $i++) {
            $table->addRow();
            $table->addCell(800)->addText($i, null, ['alignment' => Jc::CENTER]);
            $table->addCell(9500)->addText($i === 1 ? "Contoh: Jelaskan pengertian demokrasi dan sebutkan ciri-cirinya!" : "");
        }

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $fileName = 'template_soal_essay_' . now()->format('Ymd_His') . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $objWriter->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}
