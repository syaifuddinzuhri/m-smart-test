<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class QuestionShortExcelTemplateExport implements FromArray, WithHeadings, WithEvents, WithStyles
{
    public function headings(): array
    {
        return [
            ['PETUNJUK PENGISIAN SOAL JAWABAN SINGKAT'],
            ['1. Kolom NO diisi dengan nomor urut soal (sudah tersedia 1-50).'],
            ['2. Tulis teks SOAL pada kolom yang tersedia.'],
            ['3. Tulis jawaban benar pada kolom KUNCI JAWABAN.'],
            ['4. Gunakan tanda pemisah | jika ada lebih dari satu jawaban benar (Contoh: Soekarno|Ir Soekarno).'],
            [''],
            ['NO', 'SOAL', 'KUNCI JAWABAN']
        ];
    }

    public function array(): array
    {
        $data = [];
        for ($i = 1; $i <= 50; $i++) {
            $soalText = ($i === 1) ? "Contoh: Siapakah presiden pertama Republik Indonesia?" : "";
            $kunciText = ($i === 1) ? "Ir. Soekarno|Soekarno" : "";

            $data[] = [$i, $soalText, $kunciText];
        }
        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            7 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '10B981'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $questionCount = 20;
                $totalRows = $questionCount + 7;

                foreach (range(1, 5) as $row) {
                    $sheet->mergeCells("A{$row}:C{$row}");
                }

                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(80);
                $sheet->getColumnDimension('C')->setWidth(40);

                $sheet->getStyle("A7:C{$totalRows}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                        ],
                    ],
                ]);

                $sheet->getStyle("A8:A{$totalRows}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C8:C{$totalRows}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A8:C{$totalRows}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

                $sheet->getStyle("B8:B{$totalRows}")->getAlignment()->setWrapText(true);
            },
        ];
    }
}
