<?php

namespace App\Filament\Student\Pages;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class InputToken extends Page implements HasForms
{
    use InteractsWithForms;

    // 1. SEMBUNYIKAN DARI TOPBAR/SIDEBAR
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = '';
    protected static string $view = 'filament.student.pages.input-token';

    public $exam_id;
    public ?array $data = [];

    public function mount(): void
    {
        // Ambil ID Ujian dari URL
        $this->exam_id = request()->query('exam_id');

        // Jika tidak ada ID, tendang balik ke dashboard
        if (!$this->exam_id) {
            redirect()->to('/student');
        }

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('token')
                            ->label('Masukkan Token Ujian')
                            ->placeholder('******')
                            ->required()
                            ->maxLength(10)
                            ->extraInputAttributes(['style' => 'text-transform: uppercase; text-align: center; font-size: 1.5rem; font-weight: 800; letter-spacing: 0.1em;'])
                            ->autofocus(),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('validateToken')
                ->label('Verifikasi & Mulai Ujian')
                ->submit('form')
                ->color('primary')
                ->icon('heroicon-m-check-badge')
                ->size('lg'),
        ];
    }

    public function validateToken(): void
    {
        $inputData = $this->form->getState();
        $tokenInput = strtoupper($inputData['token']);

        // SIMULASI CEK TOKEN (Nanti ganti dengan pengecekan DB)
        if ($tokenInput === 'MANUSGI') {
            Notification::make()
                ->title('Token Valid')
                ->success()
                ->send();

            // Redirect ke halaman pengerjaan soal
            redirect()->to(route('filament.student.pages.start-test', ['exam_id' => $this->exam_id]));
        } else {
            Notification::make()
                ->title('Token Salah')
                ->body('Pastikan token yang Anda masukkan sudah benar.')
                ->danger()
                ->send();
        }
    }
}
