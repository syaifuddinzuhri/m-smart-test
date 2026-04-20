<?php

namespace App\Helpers;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Enums\ExamSessionStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ExamTimeHelper
{
    /**
     * Menambah waktu individu dengan logging JSON.
     */
    public static function extendSession(ExamSession $session, int $minutes, $reason = 'Tidak Ada Alasan', $reactive = false): void
    {
        DB::transaction(function () use ($session, $minutes, $reason) {
            $session->refresh();

            if (!$session->expires_at) {
                // Logika jika belum punya expires_at (tergantung sistem Anda)
                return;
            }

            $base = ($session->expires_at && $session->expires_at->isFuture())
                ? $session->expires_at
                : now();

            $newExpiresAt = $base->copy()->addMinutes($minutes);

            // Menyiapkan Log Baru
            $logs = $session->extension_log ?? [];
            $logs[] = [
                'minutes' => $minutes,
                'at' => now()->toDateTimeString(),
                'by' => Auth::user()?->name ?? 'System',
                'reason' => $reason
            ];

            $session->update([
                'expires_at' => $newExpiresAt,
                'extension_log' => $logs // Simpan Log
            ]);

            self::ensureGlobalEndTimeSufficient($session->exam, $newExpiresAt);
        });
    }

    /**
     * Menambah waktu massal dengan logging JSON.
     */
    /**
     * Menambah waktu pengerjaan untuk semua peserta ujian (Massal).
     */
    public static function extendAllSessions(Exam $exam, int $minutes, $reason = 'Tidak Ada Alasan'): void
    {
        DB::transaction(function () use ($exam, $minutes, $reason) {
            $exam->refresh();
            $adminName = auth()->user()?->name ?? 'Admin';
            $now = now();

            // 1. Tambahkan Log ke Tabel EXAMS (Riwayat Global)
            $examLogs = $exam->extension_log ?? [];
            $examLogs[] = [
                'minutes' => $minutes,
                'at' => $now->toDateTimeString(),
                'by' => $adminName,
                'reason' => $reason
            ];

            // 2. Perpanjang End Time Global
            $baseEndTime = $exam->end_time->isPast() ? $now : $exam->end_time;
            $newGlobalEndTime = $baseEndTime->addMinutes($minutes);

            $exam->update([
                'end_time' => $newGlobalEndTime,
                'extension_log' => $examLogs
            ]);

            // 2. Update Sesi Peserta
            // Gunakan each() untuk menangani UUID dengan benar sebagai Model Eloquent
            $exam->sessions()
                ->where('status', '!=', ExamSessionStatus::COMPLETED)
                ->each(function (ExamSession $session) use ($minutes, $reason) {
                    // LOGIKA UTAMA:
                    // Kita tidak menggunakan now() sebagai base, tapi langsung menambahkan ke expires_at yang ada.
                    // Dengan begini, jika Peserta A sebelumnya sudah punya bonus 10 menit (misal expires jam 10:10
                    // sementara yang lain jam 10:00), dan Admin tambah 5 menit global,
                    // Peserta A otomatis jadi 10:15 dan yang lain 10:05.

                    $currentExpiry = $session->expires_at;

                    // Jika karena suatu hal expires_at null (belum mulai), gunakan end_time ujian sebagai patokan
                    if (!$currentExpiry) {
                        return; // Atau atur logika default jika peserta belum mulai
                    }

                    $newExpiresAt = $currentExpiry->addMinutes($minutes);

                    // Siapkan log
                    $logs = $session->extension_log ?? [];
                    $logs[] = [
                        'minutes' => $minutes,
                        'at' => now()->toDateTimeString(),
                        'by' => Auth::user()?->name ?? 'Admin (Mass Update)',
                        'reason' => $reason
                    ];


                    // Sekarang $session sudah pasti instance of ExamSession (Model)
                    $session->update([
                        'expires_at' => $newExpiresAt,
                        'extension_log' => $logs
                    ]);
                });
        });
    }

    protected static function ensureGlobalEndTimeSufficient(Exam $exam, \Carbon\Carbon $newExpiresAt): void
    {
        if ($exam->end_time->lt($newExpiresAt)) {
            $exam->update(['end_time' => $newExpiresAt]);
        }
    }
}
