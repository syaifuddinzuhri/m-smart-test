<?php

namespace App\Filament\Resources;

use App\Enums\ExamSessionStatus;
use App\Filament\Resources\ExamResultResource\Traits\HasResultActions;
use App\Models\Classroom;
use App\Models\ExamCategory;
use App\Models\ExamClassroom;
use App\Models\ExamSession;
use App\Models\Subject;
use Carbon\Carbon;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;

class ExamResultResource extends Resource
{
    use HasResultActions;
    protected static ?string $model = ExamSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'Manajemen Ujian';

    protected static ?string $navigationLabel = 'Hasil Ujian';

    protected static ?string $pluralModelLabel = 'Hasil Ujian';

    protected static ?int $navigationSort = 4;

    // QUERY UTAMA: Hanya ambil yang statusnya COMPLETED
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', ExamSessionStatus::COMPLETED);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Peserta')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->user->student?->classroom?->code ?? '-'),

                TextColumn::make('exam.title')
                    ->label('Ujian')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->exam->subject?->name),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                // SKOR / NILAI
                TextColumn::make('score_pg')
                    ->label('PG')
                    ->numeric(2)
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('score_short_answer')
                    ->label('Jawaban Singkat')
                    ->numeric(2)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('score_essay')
                    ->label('Essay')
                    ->numeric(2)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_score')
                    ->label('Total')
                    ->weight(FontWeight::Bold)
                    ->color('primary')
                    ->numeric(2)
                    ->sortable(),

                TextColumn::make('violation_count')
                    ->label('Pelanggaran')
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'danger' : 'gray')
                    ->sortable(),

                // INFO PERANGKAT (Hidden by Default)
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('device_type')
                    ->label('Device')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('finished_at')
                    ->label('Selesai Pada')
                    ->formatStateUsing(function ($state) {
                        if (!$state)
                            return '-';

                        return '
                            <div class="leading-tight">
                                <div>' . $state->format('d/m/Y') . '</div>
                                <div class="text-xs text-gray-500">' . $state->format('H:i:s T') . '</div>
                            </div>
                        ';
                    })
                    ->html()
                    ->html()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('exam_category')
                    ->label('Kategori Ujian')
                    ->options(ExamCategory::pluck('name', 'id'))
                    ->query(function (Builder $query, array $data) {
                        if (filled($data['value'])) {
                            $query->whereHas('exam', fn($q) => $q->where('exam_category_id', $data['value']));
                        }
                    }),

                SelectFilter::make('classroom')
                    ->label('Kelas')
                    ->options(Classroom::pluck('code', 'id'))
                    ->query(function (Builder $query, array $data) {
                        if (filled($data['value'])) {
                            $query->whereHas('user.student', fn($q) => $q->where('classroom_id', $data['value']));
                        }
                    }),
            ], layout: FiltersLayout::Modal)
            ->actions(
                static::getMonitoringTableActions()
            )
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make(
                    static::getMonitoringBulkActions() // Memanggil method baru dari Trait
                ),
            ])
            ->extremePaginationLinks()
            ->poll('5s')
            ->defaultSort('last_activity', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Tabs::make('Detail Hasil Ujian')
                    ->tabs([
                        Infolists\Components\Tabs\Tab::make('Informasi Ujian')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Infolists\Components\Section::make('Informasi Peserta')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('user.name')->label('Nama Lengkap')->weight(FontWeight::Bold),
                                        Infolists\Components\TextEntry::make('user.student')
                                            ->label('Kelas')
                                            ->formatStateUsing(function ($state) {
                                                if (!$state || !$state->classroom)
                                                    return '-';

                                                return $state->classroom->name . ' - ' . $state->classroom->major?->name;
                                            })->weight(FontWeight::Bold),
                                        Infolists\Components\TextEntry::make('exam.title')->label('Nama Ujian')->weight(FontWeight::Bold),
                                        Infolists\Components\TextEntry::make('exam.subject.name')->label('Mata Pelajaran')->weight(FontWeight::Bold),
                                    ])->columns(2),

                                Infolists\Components\Section::make('Rincian Nilai')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('score_pg')
                                            ->label('Pilihan Ganda')
                                            ->weight(FontWeight::Bold),
                                        Infolists\Components\TextEntry::make('score_short_answer')
                                            ->label('Jawaban Singkat')
                                            ->weight(FontWeight::Bold),
                                        Infolists\Components\TextEntry::make('score_essay')
                                            ->label('Essay')
                                            ->weight(FontWeight::Bold),
                                        // Skor Akhir dengan Warna Dinamis
                                        Infolists\Components\TextEntry::make('total_score')
                                            ->label('Skor Akhir')
                                            ->weight(FontWeight::Bold)
                                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                            ->color(function ($record) {
                                                $passingGrade = ExamClassroom::where('exam_id', $record->exam_id)
                                                    ->where('classroom_id', $record->user->student?->classroom_id)
                                                    ->value('min_total_score');

                                                return (is_null($passingGrade) || $record->total_score >= $passingGrade)
                                                    ? 'success'
                                                    : 'danger';
                                            }),

                                        // Badge Status Kelulusan
                                        Infolists\Components\TextEntry::make('status_kelulusan')
                                            ->label('Status')
                                            ->getStateUsing(function ($record) {
                                                $passingGrade = ExamClassroom::where('exam_id', $record->exam_id)
                                                    ->where('classroom_id', $record->user->student?->classroom_id)
                                                    ->value('min_total_score');

                                                $isPassed = is_null($passingGrade) || ($record->total_score >= $passingGrade);

                                                return $isPassed ? 'LULUS' : 'TIDAK LULUS';
                                            })
                                            ->badge()
                                            ->color(fn(string $state): string => match ($state) {
                                                'LULUS' => 'success',
                                                'TIDAK LULUS' => 'danger',
                                                default => 'gray',
                                            }),
                                    ])->columns(5),

                                Infolists\Components\Section::make('Waktu & Aktivitas')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('started_at')->label('Waktu Mulai')->dateTime('d/m/Y, H:i:s T'),
                                        Infolists\Components\TextEntry::make('finished_at')->label('Waktu Selesai')->dateTime('d/m/Y, H:i:s T'),
                                        Infolists\Components\TextEntry::make('durasi')
                                            ->label('Durasi Pengerjaan')
                                            ->getStateUsing(function ($record) {
                                                if (!$record->started_at || !$record->finished_at)
                                                    return '-';

                                                $start = Carbon::parse($record->started_at);
                                                $end = Carbon::parse($record->finished_at);

                                                return $start->diff($end)->format('%H:%I:%S');
                                            })->icon('heroicon-o-clock'),
                                        Infolists\Components\TextEntry::make('violation_count')->label('Jumlah Pelanggaran')->badge()->color('danger'),
                                        Infolists\Components\TextEntry::make('ip_address')->label('Alamat IP'),
                                        Infolists\Components\TextEntry::make('device_type')->label('Jenis Perangkat'),
                                    ])->columns(3),
                            ]),
                        Infolists\Components\Tabs\Tab::make('Hasil Jawaban')
                            ->icon('heroicon-m-document-text')
                            ->schema([
                                Infolists\Components\ViewEntry::make('exam_results')
                                    ->view('filament.infolists.exam-result-detail')
                                    ->columnSpanFull(),
                            ])
                    ])->columnSpanFull(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\ExamResultResource\Pages\ListExamResults::route('/'),
            'view' => \App\Filament\Resources\ExamResultResource\Pages\ViewExamResult::route('/{record}'),
        ];
    }
}
