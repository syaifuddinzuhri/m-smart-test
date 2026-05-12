<?php

namespace App\Filament\Resources\ExamResource\Pages;

use App\Enums\ExamTokenType;
use App\Filament\Resources\ExamResource;
use App\Models\ExamToken;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class ManageExamTokens extends Page
{
    use InteractsWithRecord {
        configureAction as traitConfigureAction;
        afterActionCalled as traitAfterActionCalled;
        getMountedActionFormModel as traitGetMountedActionFormModel;
    }

    protected static string $resource = ExamResource::class;
    protected static string $view = 'filament.resources.exam-resource.pages.manage-exam-tokens';
    protected static ?string $title = 'Kelola Token';

    public ?string $filterType = null;

    public function setFilter(?string $type)
    {
        $this->filterType = $type;
    }

    public ?array $tokenState = [
        'type' => 'access',
        'duration' => 15, // Default 15 menit
        'quantity' => 1,
    ];

    public function mount(string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('type')
                ->label('Tipe Token')
                ->options(ExamTokenType::class)
                ->live()
                ->required(),

            Select::make('duration')
                ->label('Masa Berlaku (Menit)')
                ->options([
                    5 => '5 Menit',
                    15 => '15 Menit',
                    30 => '30 Menit',
                    60 => '1 Jam',
                    120 => '2 Jam',
                ])->required(),

            TextInput::make('quantity')
                ->label('Jumlah Kode')
                ->numeric()
                ->minValue(1)
                ->maxValue(50)
                ->required(),
        ])->statePath('tokenState');
    }

    public function getTokensQuery()
    {
        return $this->record->tokens()
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->orderBy('created_at', 'desc');
    }

    public function exportPdf()
    {
        $tokens = $this->getTokensQuery()->get();
        $exam = $this->record->load(['classrooms', 'subject']);

        $imagePath = public_path('images/logo.webp');
        $logoBase64 = '';

        if (file_exists($imagePath)) {
            $type = pathinfo($imagePath, PATHINFO_EXTENSION);
            $data = file_get_contents($imagePath);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $pdf = Pdf::loadView('pdf.exam-tokens', [
            'exam' => $exam,
            'tokens' => $tokens,
            'logo' => $logoBase64,
            'filterName' => $this->filterType ? ExamTokenType::from($this->filterType)->getLabel() : 'Semua Tipe'
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, "Token-Ujian-{$exam->id}.pdf");
    }

    public function generateBatch()
    {
        $data = $this->tokenState;
        $qty = (int) $data['quantity'];
        $isSingleUse = ($data['type'] === ExamTokenType::RELOGIN->value);

        // LOGIKA EXPIRED:
        // Jika sekarang belum mulai ujian, hitung dari start_time ujian.
        // Jika sudah lewat start_time, hitung dari jam sekarang.
        $baseTime = $this->record->start_time->isFuture()
            ? $this->record->start_time
            : now();

        $expiry = $baseTime->addMinutes((int) $data['duration']);

        for ($i = 0; $i < $qty; $i++) {
            ExamToken::create([
                'id' => Str::uuid(),
                'exam_id' => $this->record->id,
                'token' => strtoupper(Str::random(6)),
                'type' => $data['type'],
                'is_single_use' => $isSingleUse,
                'expired_at' => $expiry,
                'used_count' => 0,
                'is_active' => true,
            ]);
        }

        $this->tokenState = [
            'type' => 'access',
            'duration' => 15,
            'quantity' => 1,
        ];

        Notification::make()->title("$qty Token Berhasil Dibuat")->success()->send();
    }

    public function deleteToken($id)
    {
        ExamToken::find($id)->delete();
        Notification::make()->title('Token dihapus')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali')
                ->color('gray')
                ->url(static::getResource()::getUrl('index'))
                ->icon('heroicon-m-arrow-left'),
        ];
    }
}
