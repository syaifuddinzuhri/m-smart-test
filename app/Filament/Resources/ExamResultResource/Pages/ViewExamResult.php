<?php

namespace App\Filament\Resources\ExamResultResource\Pages;

use App\Filament\Resources\ExamResultResource;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewExamResult extends ViewRecord
{
    protected static string $resource = ExamResultResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Detail Hasil Ujian';
    }
}
