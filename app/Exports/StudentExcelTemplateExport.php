<?php

namespace App\Exports;

use App\Enums\GenderType;
use App\Models\Classroom;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentExcelTemplateExport implements FromArray, WithHeadings, WithEvents, ShouldAutoSize, WithStyles
{
    public function headings(): array
    {
        return [
            ['PETUNJUK PENGISIAN:'],
            ['1. Kolom Nama, Username, NISN, dan Kode Kelas wajib diisi.'],
            ['2. Username WAJIB huruf kecil (lowercase), tanpa spasi, dan hanya boleh huruf/angka.'],
            ['3. Kode Kelas HARUS dipilih dari dropdown yang tersedia agar tidak salah.'],
            ['4. Format Tanggal Lahir adalah YYYY-MM-DD (Contoh: 2008-05-20).'],
            ['5. PASSWORD LOGIN otomatis diset sama dengan nomor NISN siswa.'],
            [''],
            ['nama_lengkap', 'username', 'email', 'nisn', 'kode_kelas', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin']
        ];
    }

    public function array(): array
    {
        return [
            ['Syaifuddin Zuhri', 'syaifuddin', 'syaifuddin@gmail.com', '12345678', 'PILIH_DI_SINI', 'Pasuruan', '2000-01-01', 'Laki-laki'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Bold untuk seluruh petunjuk (Baris 1 sampai 6)
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true, 'color' => ['rgb' => 'E11D48']]], // Warna Merah untuk aturan Username
            4 => ['font' => ['bold' => true]],
            5 => ['font' => ['bold' => true]],
            6 => ['font' => ['bold' => true, 'color' => ['rgb' => '10B981']]], // Warna Hijau untuk info Password

            // Style untuk Header Tabel (Baris 8)
            8 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'], // Warna Indigo
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                foreach (range(1, 6) as $row) {
                    $sheet->mergeCells("A{$row}:H{$row}");
                }

                $event->sheet->getColumnDimension('A')->setAutoSize(false);
                $event->sheet->getColumnDimension('A')->setWidth(35);

                $classOptions = Classroom::where('is_active', true)->pluck('code')->toArray();
                if (!empty($classOptions)) {
                    $this->applyDropdown($event->sheet, 'E', $classOptions, 'Pilih Kelas', 'Pilih kode kelas dari daftar.');
                }

                $genderOptions = array_map(fn($case) => $case->getLabel(), GenderType::cases());
                $this->applyDropdown($event->sheet, 'H', $genderOptions, 'Pilih Gender', 'Pilih Jenis Kelamin.');
            },
        ];
    }

    /**
     * Helper untuk membuat dropdown agar kode lebih rapi
     */
    private function applyDropdown($sheet, $column, $options, $title, $prompt)
    {
        $list_values = '"' . implode(',', $options) . '"';

        for ($i = 9; $i <= 500; $i++) {
            $validation = $sheet->getCell("{$column}{$i}")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input Salah');
            $validation->setError('Silakan pilih opsi yang tersedia.');
            $validation->setPromptTitle($title);
            $validation->setPrompt($prompt);
            $validation->setFormula1($list_values);
        }
    }
}
