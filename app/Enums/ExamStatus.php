<?php

namespace App\Enums;

use ArchTech\Enums\Options;
use ArchTech\Enums\Values;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;

enum ExamStatus: string implements HasLabel, HasColor, HasIcon
{
    use Options, Values;

    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case CLOSED = 'closed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DRAFT => 'Draft (Persiapan)',
            self::ACTIVE => 'Aktif / Berlangsung',
            self::INACTIVE => 'Non-Aktif',
            self::CLOSED => 'Sudah Berakhir',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::ACTIVE => 'success',
            self::INACTIVE => 'danger',
            self::CLOSED => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::DRAFT => 'heroicon-m-pencil-square',
            self::ACTIVE => 'heroicon-m-play-circle',
            self::INACTIVE => 'heroicon-m-no-symbol',
            self::CLOSED => 'heroicon-m-lock-closed',
        };
    }
}
