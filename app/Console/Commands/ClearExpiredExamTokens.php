<?php

namespace App\Console\Commands;

use App\Models\ExamToken;
use Illuminate\Console\Command;

class ClearExpiredExamTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exam:clear-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan token ujian yang sudah kadaluarsa atau sudah digunakan (dengan buffer 1 menit)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bufferMinutes = 1;
        $threshold = now()->subMinutes($bufferMinutes);

        $deletedCount = ExamToken::query()
            ->where(function ($query) use ($threshold) {
                $query->where('expired_at', '<', $threshold) // Kasus: Waktu habis
                    ->orWhere(function ($subQuery) use ($threshold) {
                        $subQuery->where('is_single_use', true);
                    })
                    ->where('used_at', '<', $threshold);
            })
            ->delete();

        if ($deletedCount > 0) {
            $this->info("Sukses: {$deletedCount} token lama telah dihapus dari database.");
        } else {
            $this->info("Tidak ada token lama yang perlu dibersihkan.");
        }
    }
}
