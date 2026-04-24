<?php

namespace App\Filament\Supervisor\Widgets;

use App\Enums\ExamSessionStatus;
use App\Models\Exam;
use App\Models\ExamSession;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Livewire\Attributes\Reactive;

class ExamMonitorWidget extends Widget implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    protected static string $view = 'filament.supervisor.widgets.exam-monitor-widget';

    #[Reactive]
    public $filters = [];

    public ?Exam $exam = null;

    protected int | string | array $columnSpan = 'full';

    public function rendering()
    {
        $examId = data_get($this->filters, 'exam_id');

        if ($examId) {
            // Hanya query jika ID berubah atau belum ada data
            if (!$this->exam || $this->exam->id != $examId) {
                $this->exam = Exam::find($examId);
            }
        } else {
            $this->exam = null;
        }
    }

    public function getData()
    {
        $examId = data_get($this->filters, 'exam_id');
        $classroomId = data_get($this->filters, 'classroom_id');
        $search = data_get($this->filters, 'search');

        if (!$examId || !$classroomId) {
            return collect();
        }

        return ExamSession::query()
            ->with([
                'user.student',
                'exam' => fn($q) => $q->withCount('examQuestions')
            ])
            ->withCount('answers')
            ->where('exam_id', $examId)
            ->whereHas('user.student', function ($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId);
            })
            ->when($search, function ($q) use ($search) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', '%' . $search . '%'));
            })
            ->latest('last_activity')
            ->get();
    }

    // --- GLOBAL ACTIONS ---

    public function pauseAllAction(): Action
    {
        return Action::make('pauseAll')
            ->label('Pause Semua')
            ->color('gray')
            ->icon('heroicon-m-pause-circle')
            ->requiresConfirmation()
            ->modalHeading('Pause Seluruh Sesi Aktif')
            ->modalDescription('Hanya peserta dengan status SEDANG DIKERJAKAN yang akan di-pause. Peserta SELESAI tidak akan terpengaruh.')
            ->action(function () {
                $examId = data_get($this->filters, 'exam_id');
                $classroomId = data_get($this->filters, 'classroom_id');

                $affected = ExamSession::where('exam_id', $examId)
                    ->whereHas('user.student', fn($q) => $q->where('classroom_id', $classroomId))
                    ->where('status', ExamSessionStatus::ONGOING) // <--- HANYA YANG ONGOING
                    ->update([
                        'status' => ExamSessionStatus::PAUSE,
                        'token' => null,
                        'system_id' => null
                    ]);

                if ($affected > 0) {
                    Notification::make()->title("$affected sesi berhasil di-pause")->success()->send();
                } else {
                    Notification::make()->title("Tidak ada sesi aktif (Ongoing) yang ditemukan")->warning()->send();
                }
            });
    }

    public function resetAllAction(): Action
    {
        return Action::make('resetAll')
            ->label('Reset Semua')
            ->color('danger')
            ->icon('heroicon-m-trash')
            ->requiresConfirmation()
            ->modalHeading('Reset Sesi Ujian (Kecuali Selesai)')
            ->modalDescription('Apakah Anda yakin ingin MENGHAPUS sesi ujian yang BELUM SELESAI? Peserta harus mulai dari awal. Peserta yang sudah SELESAI tetap aman.')
            ->modalSubmitActionLabel('Ya, Reset Peserta Aktif')
            ->action(function () {
                $examId = data_get($this->filters, 'exam_id');
                $classroomId = data_get($this->filters, 'classroom_id');

                $affected = ExamSession::where('exam_id', $examId)
                    ->whereHas('user.student', fn($q) => $q->where('classroom_id', $classroomId))
                    ->where('status', '!=', ExamSessionStatus::COMPLETED)
                    ->delete();

                if ($affected > 0) {
                    Notification::make()->title("$affected sesi berhasil direset")->success()->send();
                } else {
                    Notification::make()->title("Tidak ada sesi aktif untuk direset")->info()->send();
                }
            });
    }

    // --- INDIVIDUAL ACTIONS ---

    public function pauseIndividualAction(): Action
    {
        return Action::make('pauseIndividual')
            ->label('Pause')
            ->icon('heroicon-s-pause')
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading('Pause Sesi Peserta')
            ->action(function (array $arguments) {
                $session = ExamSession::where('id', $arguments['id'])
                    ->where('status', ExamSessionStatus::ONGOING)
                    ->first();

                if ($session) {
                    $session->update([
                        'status' => ExamSessionStatus::PAUSE,
                        'token' => null,
                        'system_id' => null
                    ]);
                    Notification::make()->title("Sesi {$session->user->name} di-pause")->info()->send();
                } else {
                    Notification::make()->title("Gagal: Sesi tidak dalam status Ongoing")->danger()->send();
                }
            });
    }

    public function resetIndividualAction(): Action
    {
        return Action::make('resetIndividual')
            ->label('Reset')
            ->color('danger')
            ->icon('heroicon-m-trash')
            ->requiresConfirmation()
            ->modalHeading('Reset Sesi Peserta')
            ->modalDescription('Seluruh jawaban akan dihapus dan pesert mulai dari awal. Aksi ini tidak bisa dilakukan jika status sudah Selesai.')
            ->action(function (array $arguments) {
                $session = ExamSession::where('id', $arguments['id'])
                    ->where('status', '!=', ExamSessionStatus::COMPLETED)
                    ->first();

                if ($session) {
                    $name = $session->user->name;
                    $session->delete();
                    Notification::make()->title("Sesi $name berhasil direset")->success()->send();
                } else {
                    Notification::make()->title("Gagal: Sesi sudah selesai (Completed)")->danger()->send();
                }
            });
    }
}
