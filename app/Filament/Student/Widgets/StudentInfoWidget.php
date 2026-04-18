<?php

namespace App\Filament\Student\Widgets;

use App\Enums\ExamSessionStatus;
use App\Models\Exam;
use App\Models\ExamSession;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class StudentInfoWidget extends Widget
{
    protected static string $view = 'filament.student.widgets.student-info-widget';


    protected function getViewData(): array
    {
        $user = auth()->user();
        $user->load(['student.classroom.major']);

        // 1. Ujian Selesai
        $exams_done = ExamSession::where('user_id', $user->id)
            ->where('status', ExamSessionStatus::COMPLETED)
            ->count();

        // 2. Ujian Pending (Tersedia untuk kelasnya tapi belum mulai/selesai)
        $startedExamIds = ExamSession::where('user_id', $user->id)->pluck('exam_id');
        $exams_pending = Exam::whereHas('classrooms', function ($q) use ($user) {
            $q->where('classroom_id', $user->student?->classroom_id);
        })
            ->whereNotIn('id', $startedExamIds)
            ->count();

        // 3. Score Tertinggi
        $highest_score = ExamSession::where('user_id', $user->id)
            ->where('status', ExamSessionStatus::COMPLETED)
            ->max('total_score') ?? 0;

        // 4. Rata-rata Nilai
        $average_score = ExamSession::where('user_id', $user->id)
            ->where('status', ExamSessionStatus::COMPLETED)
            ->avg('total_score') ?? 0;

        return [
            'user' => $user,
            'exams_done_count' => $exams_done,
            'exams_pending_count' => $exams_pending,
            'highest_score' => $highest_score,
            'average_score' => $average_score,
        ];
    }
}
