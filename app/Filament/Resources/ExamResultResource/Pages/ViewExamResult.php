<?php

namespace App\Filament\Resources\ExamResultResource\Pages;

use App\Filament\Resources\ExamResultResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewExamResult extends ViewRecord
{
    protected static string $resource = ExamResultResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Detail Hasil Ujian';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali')
                ->color('gray')
                ->url(static::getResource()::getUrl('index'))
                ->icon('heroicon-m-arrow-left'),
            // Tombol Download PDF (Pindahan dari Infolist)
            Action::make('exportPdf')
                ->label('Download PDF')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('danger')
                // Memanggil fungsi exportPdf yang ada di class ExamResultResource
                ->action(fn() => static::getResource()::exportPdf($this->getRecord())),

            // Tombol Bantuan Skema (Header Action yang sudah ada)
            Action::make('help_scoring')
                ->label('Bantuan Skema Poin')
                ->icon('heroicon-m-information-circle')
                ->color('info')
                ->modalHeading('Simulasi Perhitungan Poin & Pinalti')
                ->modalWidth('4xl')
                ->modalSubmitAction(false)
                ->modalContent(view('filament.pages.exam-scoring-help')),
        ];
    }
}
