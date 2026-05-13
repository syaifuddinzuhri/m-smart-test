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

class QuestionEssayExcelTemplateExport implements FromArray, WithHeadings, WithEvents, WithStyles
{
    public function headings(): array
    {
        return [
            ['PETUNJUK PENGISIAN SOAL ESSAY / URAIAN'],
            ['1. Kolom NO diisi dengan nomor urut soal (sudah tersedia 1-50).'],
            ['2. Tulis teks SOAL pada kolom yang tersedia.'],
            ['3. Soal essay akan dinilai secara manual oleh pengajar.'],
            [''],
            [''],
            ['NO', 'SOAL']
        ];
    }

    public function array(): array
    {
        $data = [];
        for ($i = 1; $i <= 50; $i++) {
            $soalText = ($i === 1) ? "Contoh: Jelaskan pengertian demokrasi dan sebutkan ciri-cirinya!" : "";

            $data[] = [$i, $soalText];
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
                    'startColor' => ['rgb' => '8B5CF6'],
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
                $questionCount = 50;
                $totalRows = $questionCount + 7;

                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(100);

                foreach (range(1, 6) as $row) {
                    $sheet->mergeCells("A{$row}:B{$row}");
                }

                $sheet->getStyle("A7:B{$totalRows}")->applyFromArray([
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
                $sheet->getStyle("A8:B{$totalRows}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle("B8:B{$totalRows}")->getAlignment()->setWrapText(true);
            },
        ];
    }
}
