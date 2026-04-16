<?php

namespace App\Filament\Resources\ExamResource\Pages;

use App\Filament\Resources\ExamResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateExam extends CreateRecord
{
    protected static string $resource = ExamResource::class;

    // Override fungsi create agar dibungkus transaksi
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // parent::handleRecordCreation sudah otomatis menghandle
            // pembuatan Exam DAN penyimpanan relasi classrooms (belongsToMany)
            return parent::handleRecordCreation($data);
        });
    }

    // Redirect setelah sukses (opsional)
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
