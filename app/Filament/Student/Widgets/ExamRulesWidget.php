<?php

namespace App\Filament\Student\Widgets;

use Filament\Widgets\Widget;

class ExamRulesWidget extends Widget
{
    // Agar muncul di urutan kedua (setelah Stats, sebelum Tabel)
    protected static ?int $sort = 2;

    // Agar memenuhi lebar layar
    protected int|string|array $columnSpan = 'full';

    protected static string $view = 'filament.student.widgets.exam-rules-widget';
}
