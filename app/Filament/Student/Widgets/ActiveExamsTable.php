<?php

namespace App\Filament\Student\Widgets;

use App\Enums\ExamStatus;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Support\Enums\FontWeight;

class ActiveExamsTable extends BaseWidget
{
    protected static ?string $heading = 'Ujian Tersedia';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\User::query()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->label('Ujian / Mata Pelajaran')
                    ->default('Ujian Tengah Semester - Matematika')
                    ->weight(FontWeight::Bold)
                    ->color('primary')
                    ->description(fn() => 'Mata Pelajaran: Matematika Dasar'),

                Tables\Columns\TextColumn::make('durasi')
                    ->label('Jadwal & Durasi')
                    ->default('90 Menit')
                    ->badge()
                    ->color('success')
                    ->description(fn() => 'Dimulai: Hari ini, 08:00 WIB'),

                // KOLOM STATUS (Random berdasarkan UUID)
                Tables\Columns\TextColumn::make('status')
                    ->label('Status Pengerjaan')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        // Ubah UUID jadi angka lalu modulo 4 (jumlah case di ExamStatus)
                        $index = abs(crc32($record->id)) % 4;
                        return match ($index) {
                            0 => ExamStatus::PENDING,
                            1 => ExamStatus::NOT_STARTED,
                            2 => ExamStatus::ONGOING,
                            3 => ExamStatus::COMPLETED,
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('mulai')
                    ->label(function ($record) {
                        $index = abs(crc32($record->id)) % 4;
                        return match ($index) {
                            0 => 'Belum Dibuka',
                            1 => 'Mulai Ujian',
                            2 => 'Lanjutkan',
                            3 => 'Lihat Hasil',
                        };
                    })
                    ->icon(function ($record) {
                        $index = abs(crc32($record->id)) % 4;
                        return match ($index) {
                            0 => 'heroicon-m-clock',
                            1 => 'heroicon-m-play',
                            2 => 'heroicon-m-arrow-path',
                            3 => 'heroicon-m-document-check',
                        };
                    })
                    ->button()
                    ->color(function ($record) {
                        $index = abs(crc32($record->id)) % 4;
                        return match ($index) {
                            0 => 'gray',
                            1 => 'primary',
                            2 => 'warning',
                            3 => 'success',
                        };
                    })
                    ->url(function ($record) {
                        $index = abs(crc32($record->id)) % 4;
                        // Jika sudah selesai atau belum buka, jangan ke mana-mana dulu
                        if (in_array($index, [0, 3]))
                            return '#';

                        return route('filament.student.pages.input-token', ['exam_id' => $record->id]);
                    })
                    ->disabled(fn($record) => (abs(crc32($record->id)) % 4) === 0),
            ])
            ->paginated(false);
    }
}
