<?php

namespace App\Console\Commands;

use App\Enums\ExamStatus;
use App\Models\Exam;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateExamStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exam:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis update status ujian berdasarkan start_time dan end_time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $activated = Exam::where('status', ExamStatus::DRAFT->value)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->update(['status' => ExamStatus::ACTIVE->value]);

        $closed = Exam::where('end_time', '<=', $now)
            ->update(['status' => ExamStatus::CLOSED->value]);

        if ($activated > 0 || $closed > 0) {
            $this->info("Berhasil: {$activated} ujian diaktifkan, {$closed} ujian ditutup.");
        }
    }
}
