<?php

namespace App\Filament\Student\Widgets;

use Filament\Widgets\Widget;

class StudentInfoWidget extends Widget
{
    // Menggunakan file blade yang kita buat tadi
    protected static string $view = 'filament.student.widgets.student-info-widget';

    // Atur agar widget mengambil lebar penuh (full width)
    protected int|string|array $columnSpan = 'full';

    public function getData(): array
    {
        // Anda bisa mengambil data user yang sedang login di sini
        // $user = Auth::user();
        return [
            // 'student' => $user->student
        ];
    }
}
