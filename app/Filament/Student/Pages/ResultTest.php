<?php

namespace App\Filament\Student\Pages;

use App\Enums\ExamSessionStatus;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamCategory;
use App\Models\ExamClassroom;
use App\Models\ExamSession;
use App\Models\Subject;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ResultTest extends Page implements HasTable
{
    use InteractsWithTable;
    protected static ?string $navigationLabel = 'Hasil Ujian';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.student.pages.result-test';

    public function getTitle(): string|Htmlable
    {
        return 'Riwayat Hasil Ujian';
    }

    public function getHeading(): string|Htmlable
    {
        return new HtmlString('
        <div class="flex flex-col gap-1">
            <span class="text-2xl font-extrabold text-gray-900 leading-tight uppercase">
                Riwayat Hasil Ujian
            </span>
        </div>
    ');
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $userId = $user->id;
        $classroomId = $user->student?->classroom_id;

        return $table
            ->query(
                Exam::query()
                    ->whereHas('sessions', function ($query) use ($userId) {
                        $query->where('user_id', $userId)
                            ->where('status', ExamSessionStatus::COMPLETED);
                    })
                    ->addSelect([
                        'finished_at' => ExamSession::select('finished_at')
                            ->whereColumn('exam_id', 'exams.id')
                            ->where('user_id', $userId)
                            ->latest()
                            ->take(1),
                        'student_score' => ExamSession::select('total_score')
                            ->whereColumn('exam_id', 'exams.id')
                            ->where('user_id', $userId)
                            ->latest()
                            ->take(1),
                        'passing_grade' => ExamClassroom::select('min_total_score')
                            ->whereColumn('exam_id', 'exams.id')
                            ->where('classroom_id', $classroomId)
                            ->take(1),
                        'target_classroom' => Classroom::query()
                            ->where('classrooms.id', $classroomId)
                            ->select('name')
                            ->take(1),
                    ])
                    ->with([
                        'sessions' => function ($query) use ($userId) {
                            $query->where('user_id', $userId);
                        },
                        'category',
                        'subject'
                    ])
            )
            ->defaultSort('finished_at')
            ->searchable()
            ->filters([
                SelectFilter::make('exam_category_id')
                    ->label('Kategori')
                    ->options(ExamCategory::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('subject_id')
                    ->label('Mata Pelajaran')
                    ->options(Subject::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
            ])
            ->columns(self::buildColumns())
            ->actions([
                Action::make('detail')
                    ->label('Lihat Detail')
                    ->icon('heroicon-m-eye')
                    ->button()
                    ->color('gray')
                    ->outlined()
                    ->extraAttributes(['class' => 'w-full md:w-auto mt-4 justify-center'])
                    ->url(fn($record) => DetailResultTest::getUrl([
                        'record' => $record->id
                    ]))
            ]);
    }

    protected static function buildColumns()
    {
        return [
            Stack::make([
                TextColumn::make('title')
                    ->label('Judul')
                    ->weight(FontWeight::Bold)
                    ->size(TextColumn\TextColumnSize::Large),
                ViewColumn::make('full_info')
                    ->label('Informasi Hasil Ujian')
                    ->view('filament.tables.columns.exam-session-info')
            ])->space(2),
        ];
    }
}
