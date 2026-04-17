<?php

namespace App\Enums;

use ArchTech\Enums\Options;
use ArchTech\Enums\Values;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum GenderType: string implements HasLabel, HasColor
{
    use Options, Values;
    case MALE = 'male';
    case FEMALE = 'female';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MALE => 'Laki-laki',
            self::FEMALE => 'Perempuan',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::MALE => 'info',
            self::FEMALE => 'danger',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->getLabel()
        ])->toArray();
    }
}
