<?php

namespace App\Filament\Pages;

use App\Exports\QuestionPgTemplateExport;
use App\Exports\QuestionPgWordTemplateExport;
use App\Imports\QuestionPgImport;
use App\Imports\QuestionPgWordImport;
use App\Models\QuestionCategory;
use App\Models\Subject;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;

class ImportQuestion extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Import Soal';
    protected static ?string $title = 'Import Soal';
    protected static ?string $navigationGroup = 'Manajemen Soal';
    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.import-question';
    public array $failures = [];
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Konfigurasi Import Soal')
                    ->description('Lengkapi data berikut dan pilih file template yang sesuai.')
                    ->schema([
                        Group::make([
                            Select::make('question_category_id')
                                ->label('Topik / Kategori')
                                ->options(QuestionCategory::pluck('name', 'id'))
                                ->required(),
                            Select::make('subject_id')
                                ->label('Mata Pelajaran')
                                ->options(Subject::pluck('name', 'id'))
                                ->reactive()
                                ->required(),
                        ])->columnSpan(1),

                        Group::make([
                            Select::make('type')
                                ->label('Tipe Soal dalam File')
                                ->options([
                                    'pg' => 'Pilihan Ganda (Single & Multiple)',
                                    'tf' => 'True / False',
                                    'short' => 'Jawaban Singkat',
                                    'essay' => 'Essay',
                                ])
                                ->required(),

                            FileUpload::make('file')
                                ->label('File Template (Excel atau Word)')
                                ->helperText('Unggah file .xlsx atau .docx sesuai template yang tersedia.')
                                ->acceptedFileTypes([
                                    // MIME type untuk Excel (.xlsx)
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    // MIME type untuk Word (.docx)
                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                                ])
                                ->disk('local')
                                ->directory('temp-imports')
                                ->required()
                                ->preserveFilenames()
                                ->rules(['extensions:xlsx,docx'])
                                ->extraAttributes(['class' => 'h-full']),
                        ])->columnSpan(1),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadTemplate')
                ->label('Download Template')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->modalWidth('md')
                ->modalHeading('Pilih Tipe Template')
                ->modalDescription('Silakan pilih tipe soal untuk mengunduh format Excel yang sesuai.')
                ->modalSubmitActionLabel('Unduh Sekarang')
                ->modalCancelActionLabel('Kembali')
                ->form([
                    Select::make('format')
                        ->label('Format File')
                        ->options([
                            'excel' => 'Microsoft Excel (.xlsx)',
                            'word' => 'Microsoft Word (.docx)',
                        ])->default('excel')->required(),
                    Select::make('template_type')
                        ->label('Pilih Tipe Template')
                        ->options([
                            'pg' => 'Pilihan Ganda (Single & Multiple)',
                            'tf' => 'True / False',
                            'short' => 'Jawaban Singkat',
                            'essay' => 'Essay',
                        ])->required()
                ])
                ->action(function (array $data) {
                    if ($data['format'] === 'word') {
                        return match ($data['template_type']) {
                            'pg' => QuestionPgWordTemplateExport::export(),
                            default => Notification::make()->title('Template belum tersedia')->danger()->send(),
                        };
                    }

                    return match ($data['template_type']) {
                        'pg' => Excel::download(new QuestionPgTemplateExport, 'template_soal_pilihan_ganda.xlsx'),
                        default => Notification::make()->title('Template belum tersedia')->danger()->send(),
                    };
                }),
        ];
    }

    public function submit()
    {
        $this->resetErrorBag();
        $state = $this->form->getState();
        $this->failures = [];

        DB::beginTransaction();
        try {
            $filePath = Storage::disk('local')->path($state['file']);
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            if ($state['type'] === 'pg') {
                if ($extension === 'docx') {
                    $import = new QuestionPgWordImport(
                        $state['subject_id'],
                        $state['question_category_id']
                    );
                    $import->import($filePath);
                } else {
                    $import = new QuestionPgImport($state['subject_id'], $state['question_category_id']);
                    Excel::import($import, $filePath);
                }
            }

            Notification::make()
                ->title('Proses Import Berhasil')
                ->success()
                ->send();

            Storage::disk('local')->delete($state['file']);
            $this->form->fill();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            // Ambil daftar kegagalan dari class import jika ada
            if (isset($import) && !empty($import->importErrors)) {
                $this->failures = $import->importErrors;

                Notification::make()
                    ->title('Import Gagal')
                    ->body('Terdapat beberapa kesalahan. Data tidak ada yang disimpan.')
                    ->danger()
                    ->send();
            } else {
                // Error sistem lainnya (file tidak ketemu, database mati, dll)
                Notification::make()
                    ->title('Terjadi Kesalahan Sistem')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }
        }
    }
}
