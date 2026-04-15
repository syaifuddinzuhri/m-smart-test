<?php

namespace App\Filament\Student\Pages;

use Filament\Pages\Page;

class DetailResultTest extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Detail Hasil Ujian';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.student.pages.detail-result-test';

    public $examData = [];
    public $results = [];

    public function mount()
    {
        // Simulasi Data Summary
        $this->examData = [
            'judul' => 'Ujian Akhir Semester - Teknologi Informasi',
            'skor' => 80,
            'total_soal' => 5,
            'benar' => 4,
            'salah' => 1,
            'waktu_selesai' => '15 April 2026, 11:20 WIB',
        ];

        // Simulasi Data Soal Lengkap Berbagai Tipe
        $this->results = [
            // 1. Pilihan Ganda (Single Choice)
            [
                'no' => 1,
                'tipe' => 'PG',
                'pertanyaan' => 'Apa singkatan dari HTML?',
                'options' => [
                    'a' => 'Hyper Tool Markup Language',
                    'b' => 'Hyper Text Markup Language',
                    'c' => 'Hyper Text Management Language',
                    'd' => 'Hyper Tool Management Language',
                ],
                'jawaban_siswa' => 'b', // Benar
                'is_correct' => false,
            ],

            // 2. Multiple Choice (Pilih Banyak)
            [
                'no' => 2,
                'tipe' => 'Multiple Choice',
                'pertanyaan' => 'Mana saja yang termasuk Framework CSS? (Pilih 2)',
                'options' => [
                    'a' => 'Tailwind CSS',
                    'b' => 'Laravel',
                    'c' => 'Bootstrap',
                    'd' => 'NestJS',
                ],
                'jawaban_siswa' => ['a', 'c'], // Jawaban dalam bentuk array
                'is_correct' => true,
            ],

            // 3. Radio Button (True/False)
            [
                'no' => 3,
                'tipe' => 'Radio',
                'pertanyaan' => 'PHP adalah bahasa pemrograman sisi klien (Client-side).',
                'options' => [
                    'true' => 'Benar',
                    'false' => 'Salah',
                ],
                'jawaban_siswa' => 'true', // Salah (Harusnya False)
                'is_correct' => false,
            ],

            // 4. Short Answer (Isian Singkat)
            [
                'no' => 4,
                'tipe' => 'Short Answer',
                'pertanyaan' => 'Sebutkan nama database yang sering digunakan bersama Laravel secara default!',
                'options' => null, // Tidak ada pilihan
                'jawaban_siswa' => 'MySQL',
                'is_correct' => true,
            ],

            // 5. Essay (Uraian)
            [
                'no' => 5,
                'tipe' => 'Essay',
                'pertanyaan' => 'Jelaskan secara singkat kegunaan dari Middleware pada aplikasi web!',
                'options' => null,
                'jawaban_siswa' => 'Middleware berfungsi sebagai jembatan antara request dan response. Biasanya digunakan untuk autentikasi atau filter keamanan sebelum user masuk ke controller utama.',
                'is_correct' => true,
            ],
        ];
    }
}
