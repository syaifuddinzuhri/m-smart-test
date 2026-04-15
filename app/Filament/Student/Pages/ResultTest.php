<?php

namespace App\Filament\Student\Pages;

use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;

class ResultTest extends Page implements HasTable
{
    use InteractsWithTable;
    protected static ?string $navigationLabel = 'Hasil Ujian';
    protected static ?string $title = 'Riwayat & Hasil Ujian';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.student.pages.result-test';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\User::query()
            )
            ->searchable()
            ->filters([
                SelectFilter::make('mapel')
                    ->label('Mata Pelajaran')
                    ->options([
                        'matematika' => 'Matematika',
                        'bahasa_indonesia' => 'Bahasa Indonesia',
                        'ipa' => 'IPA',
                    ]),
                SelectFilter::make('kategori')
                    ->label('Kategori Ujian')
                    ->options([
                        'uts' => 'UTS',
                        'uas' => 'UAS',
                        'harian' => 'Ulangan Harian',
                    ]),
                SelectFilter::make('status')
                    ->label('Status Kelulusan')
                    ->options([
                        'lulus' => 'Lulus',
                        'tidak_lulus' => 'Tidak Lulus',
                    ]),
            ])
            ->columns([
                Stack::make([
                    TextColumn::make('judul_ujian')
                        ->label('Judul')
                        ->default('Ujian Akhir Semester - Bahasa Indonesia')
                        ->weight(FontWeight::Bold)
                        ->size(TextColumn\TextColumnSize::Large),

                    TextColumn::make('tanggal')
                        ->default('Selesai pada: 12 April 2026')
                        ->size(TextColumn\TextColumnSize::ExtraSmall)
                        ->color('gray')
                        ->icon('heroicon-m-calendar-days'),

                    Split::make([
                        TextColumn::make('nilai')
                            ->getStateUsing(fn() => 'Skor: 85/100')
                            ->badge()
                            ->color('success')
                            ->weight(FontWeight::Black),

                        TextColumn::make('status')
                            ->default('LULUS')
                            ->badge()
                            ->color('success'),
                    ])->extraAttributes(['class' => 'mt-3 mb-2']),
                ])->space(2),
            ])
            ->actions([
                Action::make('detail')
                    ->label('Lihat Detail')
                    ->icon('heroicon-m-eye')
                    ->button()
                    ->outlined()
                    ->extraAttributes(['class' => 'w-full md:w-auto mt-4 justify-center'])
                    ->url(fn($record) => '#'),
            ]);
    }
}
