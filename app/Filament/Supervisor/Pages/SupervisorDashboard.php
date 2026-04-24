<?php

namespace App\Filament\Supervisor\Pages;

use App\Models\Classroom;
use App\Models\Exam;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Page;

class SupervisorDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static string $view = 'filament.supervisor.pages.supervisor-dashboard';
    protected static ?string $title = 'Monitoring Ujian';

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'exam_id' => null,
            'classroom_id' => null,
            'search' => null,
        ]);
    }

    protected function getFormModel(): string
    {
        return 'array';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('exam_id')
                    ->label('Pilih Ujian')
                    ->options(Exam::pluck('title', 'id'))
                    ->searchable()
                    ->live(),
                Select::make('classroom_id')
                    ->label('Pilih Kelas')
                    ->options(Classroom::pluck('name', 'id'))
                    ->searchable()
                    ->live(),
                TextInput::make('search')
                    ->label('Cari Nama Siswa')
                    ->placeholder('Ketik nama...')
                    ->live(onBlur: false)
                    ->disabled(fn(Get $get) => ! $get('exam_id') || ! $get('classroom_id'))
                    ->hint(fn(Get $get) => (! $get('exam_id') || ! $get('classroom_id')) ? 'Pilih ujian & kelas dulu' : null),
            ])
            ->columns([
                'default' => 1,
                'sm' => 3,
            ])
            ->statePath('data');
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }
}
