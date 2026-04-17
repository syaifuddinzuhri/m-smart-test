<?php

namespace App\Filament\Student\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class StudentInfoWidget extends Widget
{
    // Menggunakan file blade yang kita buat tadi
    protected static string $view = 'filament.student.widgets.student-info-widget';

    // Atur agar widget mengambil lebar penuh (full width)
    protected int|string|array $columnSpan = 'full';

    public function getViewData(): array
    {
        $user = Auth::user();
        $user->load(['student.classroom.major']);

        return [
            'user' => $user
        ];
    }
}
