<?php

namespace App\Filament\Pages;

use App\Enums\ExamSessionStatus;
use App\Enums\ExamStatus;
use App\Enums\UserRole;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\User;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.dashboard';

    public function getStats(): array
    {
        return [
            // Statistik Umum
            'total_participants' => User::where('role', UserRole::STUDENT->value)->count(),
            'total_questions' => Question::count(),
            'ongoing_sessions' => ExamSession::where('status', ExamSessionStatus::ONGOING->value)->count(),

            // Statistik Berdasarkan Enum ExamStatus
            'exam_active' => Exam::where('status', ExamStatus::ACTIVE)->count(),
            'exam_draft' => Exam::where('status', ExamStatus::DRAFT)->count(),
            'exam_closed' => Exam::where('status', ExamStatus::CLOSED)->count(),
        ];
    }
}
