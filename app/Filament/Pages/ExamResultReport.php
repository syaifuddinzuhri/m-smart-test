<?php

namespace App\Filament\Pages;

use App\Exports\ExamResultExport;
use App\Models\Classroom;
use App\Models\Exam;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ExamResultReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Rekap Hasil Ujian';
    protected static ?string $title = 'Laporan Rekap Hasil Ujian';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.exam-result-report';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Rekapitulasi')
                    ->description('Pilih ujian dan kelas untuk mengunduh rekap hasil dalam format Excel.')
                    ->schema([
                        Select::make('exam_id')
                            ->label('Pilih Ujian')
                            ->options(Exam::query()->latest()->pluck('title', 'id'))
                            ->searchable()
                            ->required(),

                        Select::make('classroom_id')
                            ->label('Pilih Kelas')
                            ->options(
                                Classroom::with('major')->get()->mapWithKeys(fn($c) => [
                                    $c->id => "{$c->name} - {$c->major->name}"
                                ])
                            )
                            ->searchable()
                            ->required(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('download')
                ->label('Unduh Rekap Excel')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('success')
                ->action('downloadExcel'),
        ];
    }

    public function downloadExcel()
    {
        $formData = $this->form->getState();

        $exam = Exam::find($formData['exam_id']);
        $classroom = Classroom::find($formData['classroom_id']);

        if (!$exam || !$classroom) {
            Notification::make()->title('Filter tidak valid')->danger()->send();
            return;
        }

        $fileNameString = "rekap hasil {$exam->title} {$classroom->name} - {$classroom->major?->name}";
        $fileName = Str::slug($fileNameString) . '.xlsx';

        DB::beginTransaction();
        try {
            return Excel::download(
                new ExamResultExport($formData['exam_id'], $formData['classroom_id']),
                $fileName
            );
        } catch (\Throwable $th) {
            Notification::make()->title('Terjadi Kesalahan Export')->body($th->getMessage())->danger()->send();
        }
    }
}
