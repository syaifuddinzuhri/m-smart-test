<?php

namespace App\Enums;

enum QuestionGroupType: string
{
    case PG = 'pg';
    case TF = 'tf';
    case SHORT = 'short';
    case ESSAY = 'essay';

    // Mendapatkan Label Manusiawi
    public function getLabel(): string
    {
        return match ($this) {
            self::PG => 'Pilihan Ganda',
            self::TF => 'Benar / Salah',
            self::SHORT => 'Isian Singkat',
            self::ESSAY => 'Essay / Uraian',
        };
    }

    // Mendapatkan Warna (Untuk Badge di UI)
    public function getColor(): string
    {
        return match ($this) {
            self::PG => 'success',   // Hijau
            self::TF => 'info',      // Biru/Indigo
            self::SHORT => 'warning',// Kuning/Oranye
            self::ESSAY => 'danger', // Merah/Ungu
        };
    }

    // Untuk digunakan di Select Option Filament
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(function ($case) {
            return [$case->value => $case->getLabel()];
        })->toArray();
    }

    public function getTemplateKeyword(): string
    {
        return match ($this) {
            self::PG => 'PETUNJUK PENGISIAN SOAL PILIHAN GANDA',
            self::TF => 'PETUNJUK PENGISIAN SOAL BENAR/SALAH',
            self::SHORT => 'PETUNJUK PENGISIAN SOAL ISIAN SINGKAT',
            self::ESSAY => 'PETUNJUK PENGISIAN SOAL ESSAY / URAIAN',
        };
    }
}
