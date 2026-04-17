<?php

namespace App\Enums;

use ArchTech\Enums\Options;
use ArchTech\Enums\Values;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ExamTokenType: string implements HasLabel, HasColor
{
    use Options, Values;
    case ACCESS = 'access';
    case RELOGIN = 'relogin';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ACCESS => 'Akses Masuk Awal',
            self::RELOGIN => 'Akses Masuk Ulang',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ACCESS => 'success',
            self::RELOGIN => 'danger',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->getLabel()
        ])->toArray();
    }
}
