<?php

namespace App\Filament\Student\Widgets;

use App\Enums\ExamSessionStatus;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;

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
                Stack::make([
                    Tables\Columns\TextColumn::make('judul')
                        ->label('Ujian / Mata Pelajaran')
                        ->default('Ujian Tengah Semester - Matematika')
                        ->weight(FontWeight::Bold)
                        ->color('primary')
                        ->size(Tables\Columns\TextColumn\TextColumnSize::Large),

                    Tables\Columns\TextColumn::make('mapel')
                        ->default('Mata Pelajaran: Matematika Dasar')
                        ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                        ->color('gray'),

                    Split::make([
                        Tables\Columns\TextColumn::make('durasi')
                            ->default('90 Menit')
                            ->badge()
                            ->color('danger'),

                        Tables\Columns\TextColumn::make('status')
                            ->badge()
                            ->getStateUsing(function ($record) {
                                $index = abs(crc32($record->id)) % 4;
                                return match ($index) {
                                    0 => ExamSessionStatus::PENDING,
                                    1 => ExamSessionStatus::NOT_STARTED,
                                    2 => ExamSessionStatus::ONGOING,
                                    3 => ExamSessionStatus::COMPLETED,
                                };
                            }),
                    ])->extraAttributes(['class' => 'mt-3 mb-2']),

                    Tables\Columns\TextColumn::make('jadwal')
                        ->default('Dimulai: Hari ini, 08:00 WIB')
                        ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                        ->icon('heroicon-m-calendar-days')
                        ->color('gray'),
                ])->space(2),
            ])
            ->actions([
                Tables\Actions\Action::make('mulai')
                    ->label(function ($record) {
                        $index = abs(crc32($record->id)) % 4;
                        return match ($index) {
                            0 => 'Belum Mulai',
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
                    ->extraAttributes([
                        'class' => 'w-full md:w-auto mt-4 justify-center',
                    ])
                    ->url(function ($record) {
                        $index = abs(crc32($record->id)) % 4;
                        if (in_array($index, [0, 3]))
                            return '#';

                        return route('filament.student.pages.input-token', ['exam_id' => $record->id]);
                    })
                    ->disabled(fn($record) => (abs(crc32($record->id)) % 4) === 0),
            ])
            ->paginated(false);
    }
}
