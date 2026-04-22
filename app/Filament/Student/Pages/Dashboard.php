<?php

namespace App\Filament\Student\Pages;

use App\Enums\ExamSessionStatus;
use App\Models\ExamSession;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.student.pages.dashboard';

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    public $lastSnapshotUrl = null;

    // Method untuk menerima snapshot dari JS
    public function uploadSnapshot($imageData)
    {
        $image = str_replace('data:image/jpeg;base64,', '', $imageData);
        $image = str_replace(' ', '+', $image);
        $imageName = 'test_snapshot_' . auth()->id() . '.jpg';

        Storage::disk('public')->put('snapshots/' . $imageName, base64_decode($image));

        $this->lastSnapshotUrl = asset('storage/snapshots/' . $imageName) . '?v=' . time();

        // Opsional: Beri notifikasi kecil
        // $this->dispatch('snapshot-uploaded');
    }

    public function mount()
    {
        /**
         * LOGIC RESET AKSES GLOBAL:
         * Setiap kali siswa masuk ke Dashboard, cari semua sesi ujian milik siswa ini
         * yang masih memiliki token/system_id aktif, lalu set menjadi null.
         * Ini adalah lapis keamanan jika siswa berhasil 'back' ke Dashboard.
         */
        ExamSession::where('user_id', Auth::id())
            ->where(function ($query) {
                $query->whereNotNull('token')
                    ->orWhereNotNull('system_id');
            })
            ->update([
                'token' => null,
                'system_id' => null,
                'status' => ExamSessionStatus::PAUSE
            ]);
    }
}
