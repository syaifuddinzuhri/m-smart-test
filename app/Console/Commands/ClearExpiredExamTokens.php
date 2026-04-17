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
    protected $description = 'Membersihkan token ujian yang sudah kadaluarsa atau sudah digunakan (dengan buffer 5 menit)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bufferMinutes = 5;
        $threshold = now()->subMinutes($bufferMinutes);

        /**
         * Logika Penghapusan:
         * 1. Hapus jika expired_at sudah lewat dari 5 menit yang lalu.
         * 2. Hapus jika token single_use dan used_at sudah lewat dari 5 menit yang lalu.
         */
        $deletedCount = ExamToken::query()
            ->where(function ($query) use ($threshold) {
                $query->where('expired_at', '<', $threshold) // Kasus: Waktu habis
                    ->orWhere(function ($subQuery) use ($threshold) {
                        $subQuery->where('is_single_use', true)
                            ->whereNotNull('used_at') // Kasus: Sudah terpakai
                            ->where('used_at', '<', $threshold);
                    });
            })
            ->delete();

        if ($deletedCount > 0) {
            $this->info("Sukses: {$deletedCount} token lama telah dihapus dari database.");
        } else {
            $this->info("Tidak ada token lama yang perlu dibersihkan.");
        }
    }
}
