<?php

namespace App\Filament\Student\Pages;

use App\Enums\ExamSessionStatus;
use App\Models\Exam;
use App\Models\ExamSession;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;

class StartTest extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Simulasi Ujian Online';

    protected static string $view = 'filament.student.pages.start-test';

    public $activeTab = 'pg';
    public $currentStep = 1;
    public $totalPG = 10;
    public $totalEssay = 2;

    public ?array $data = [];

    public $doubtfulQuestions = [];
    public $durationInSeconds = 0;

    public bool $isLocked = false;

    public ?string $token = null;

    public ?Exam $exam = null;
    public ?ExamSession $session = null;

    public function getHeader(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.student.components.hide-nav-css');
    }

    public function mount()
    {
        $this->token = request()->query('token');

        if (!$this->token) {
            return redirect()->to('/student');
        }

        $tokenHash = hash('sha256', $this->token);

        $this->session = ExamSession::where('token', $tokenHash)
            ->first();

        if (!$this->session) {
            return redirect()->to('/student');
        }

        $this->exam = Exam::find($this->session->exam_id);

        if (!$this->exam) {
            return redirect()->to('/student');
        }

        if ($this->session->status === ExamSessionStatus::PAUSE) {
            $this->isLocked = true;
        }

        if ($this->session) {
            $this->durationInSeconds = $this->session->remaining_duration;
        } else {
            $this->durationInSeconds = $this->exam->duration * 60;
        }

        $this->form->fill();
    }

    public function lockExam(): void
    {
        $updateData = [
            'status' => ExamSessionStatus::PAUSE->value,
            'last_violation_at' => now(),
            'violation_count' => ($this->exam->violation_count ?? 0) + 1,
        ];
        $this->session->update($updateData);
        $this->isLocked = true;
        $this->dispatch('exit-fullscreen');
    }

    public function updateRemainingTime($seconds): void
    {
        if ($this->session) {
            $this->session->update([
                'remaining_duration' => $seconds
            ]);
        }
    }

    public function timeOut(): void
    {
        $this->submit();
        Notification::make()
            ->title('Waktu Habis')
            ->body('Ujian otomatis tersimpan karena waktu telah selesai.')
            ->warning()
            ->send();
    }

    public function backToDashboard()
    {
        $updateData = [
            'token' => null,
            'system_id' => null
        ];
        $this->session->update($updateData);
        $this->dispatch('prepare-navigation');
        return redirect()->to('/student/input-token?exam_id=' . $this->exam->id);
    }

    public function toggleDoubt($key)
    {
        if (in_array($key, $this->doubtfulQuestions)) {
            $this->doubtfulQuestions = array_diff($this->doubtfulQuestions, [$key]);
        } else {
            $this->doubtfulQuestions[] = $key;
        }
    }

    public function goToStep($tab, $step)
    {
        $this->activeTab = $tab;
        $this->currentStep = $step;
    }

    // Fungsi pembantu untuk cek status di Blade
    public function getQuestionStatus($key)
    {
        $isAnswered = !empty($this->data[$key]);
        $isDoubtful = in_array($key, $this->doubtfulQuestions);

        if ($isDoubtful)
            return 'doubtful';
        if ($isAnswered)
            return 'answered';
        return 'unanswered';
    }

    public function submitAction(): Action
    {
        return Action::make('submit')
            ->label('Submit & Kirim Ujian')
            ->icon('heroicon-m-paper-airplane')
            ->color('info')
            ->size('md')
            ->extraAttributes([
                'class' => 'w-full md:w-auto justify-center',
            ])
            ->requiresConfirmation()
            ->modalHeading('Kirim Jawaban Ujian?')
            ->modalDescription('Pastikan semua jawaban sudah benar. Setelah dikirim, Anda tidak dapat mengubah jawaban lagi.')
            ->modalSubmitActionLabel('Ya, Kirim Sekarang')
            ->modalCancelActionLabel('Batal')
            ->modalIcon('heroicon-o-check-circle')
            ->modalAlignment(Alignment::Center)
            ->action(fn() => $this->submit());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // KELOMPOK SOAL PILIHAN GANDA
                \Filament\Forms\Components\Group::make([
                    Radio::make('q1')
                        ->label('1. Apa benar pernyataan ini?')
                        ->options(['a' => 'Benar', 'b' => 'Salah'])
                        ->live()
                        ->visible(fn() => $this->activeTab === 'pg' && $this->currentStep === 1),

                    Radio::make('q2')
                        ->label('2. Laravel menggunakan bahasa pemrograman apa?')
                        ->options(['a' => 'A. PHP', 'b' => 'B. Javascript', 'c' => 'C. Ruby'])
                        ->live()
                        ->visible(fn() => $this->activeTab === 'pg' && $this->currentStep === 2),

                    CheckboxList::make('q3')
                        ->label('3. Pilih framework CSS (Bisa lebih dari satu)')
                        ->options(['a' => 'A. Tailwind', 'b' => 'B. Bootstrap', 'c' => 'C. Laravel'])
                        ->live()
                        ->visible(fn() => $this->activeTab === 'pg' && $this->currentStep === 3),
                ]),

                // KELOMPOK SOAL ESSAY
                \Filament\Forms\Components\Group::make([
                    TextInput::make('q4')
                        ->label('1. Siapa penemu World Wide Web?')
                        ->live()
                        ->visible(fn() => $this->activeTab === 'essay' && $this->currentStep === 1),

                    RichEditor::make('q5')
                        ->label('2. Jelaskan perbedaan Frontend dan Backend!')
                        ->live()
                        ->visible(fn() => $this->activeTab === 'essay' && $this->currentStep === 2),
                ]),
            ])
            ->statePath('data');
    }

    // Navigasi Next
    public function next()
    {
        if ($this->activeTab === 'pg') {
            if ($this->currentStep < $this->totalPG) {
                $this->currentStep++;
            } else {
                // Jika PG sudah habis, pindah ke Essay
                $this->activeTab = 'essay';
                $this->currentStep = 1;
            }
        } else {
            if ($this->currentStep < $this->totalEssay) {
                $this->currentStep++;
            }
        }
    }

    // Navigasi Prev
    public function previous()
    {
        if ($this->activeTab === 'essay') {
            if ($this->currentStep > 1) {
                $this->currentStep--;
            } else {
                // Kembali ke PG soal terakhir
                $this->activeTab = 'pg';
                $this->currentStep = $this->totalPG;
            }
        } else {
            if ($this->currentStep > 1) {
                $this->currentStep--;
            }
        }
    }

    // Ganti Tab Manual
    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->currentStep = 1;
    }

    public function isAllAnswered(): bool
    {
        $totalSoal = $this->totalPG + $this->totalEssay;

        // Filter data: hapus yang null, string kosong, atau array kosong
        $answeredData = collect($this->data)->filter(function ($value) {
            if (is_array($value)) {
                return count($value) > 0;
            }
            return !empty($value);
        });

        return $answeredData->count() >= $totalSoal;
    }

    public function submit()
    {
        $jawaban = $this->form->getState();

        // Simulasi notifikasi sukses
        Notification::make()
            ->title('Jawaban Berhasil Terkirim')
            ->body('Terima kasih, jawaban ujian Anda telah kami terima.')
            ->success()
            ->send();

        // Redirect kembali ke daftar ujian setelah 2 detik (opsional)
        return redirect()->to('/student');
    }
}
