<?php

namespace App\Enums;

use ArchTech\Enums\Options;
use ArchTech\Enums\Values;
use Filament\Support\Contracts\HasLabel;

enum QuestionAttachmentType: string implements HasLabel
{
    use Options, Values;

    case IMAGE = 'image';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case FILE = 'file';

    public function getLabel(): string
    {
        return match ($this) {
            self::AUDIO => 'Audio',
            self::VIDEO => 'Video',
            self::FILE => 'File',
            self::IMAGE => 'Gambar',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->getLabel()
        ])->toArray();
    }
}
