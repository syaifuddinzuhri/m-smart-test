<?php

namespace App\Enums;

use ArchTech\Enums\Options;
use ArchTech\Enums\Values;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    use Options, Values;

    case ADMIN = 'admin';
    case TEACHER = 'teacher';
    case STUDENT = 'student';
    case SUPERVISOR = 'supervisor';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::TEACHER => 'Guru',
            self::STUDENT => 'Peserta',
            self::SUPERVISOR => 'Pengawas',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::TEACHER => 'Guru',
            self::STUDENT => 'Peserta',
            self::SUPERVISOR => 'Pengawas',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->getLabel()
        ])->toArray();
    }

    public static function withoutStudent(): array
    {
        return collect(self::cases())
            ->reject(fn($case) => $case === self::STUDENT)
            ->mapWithKeys(fn($case) => [
                $case->value => $case->getLabel(),
            ])
            ->toArray();
    }
}
