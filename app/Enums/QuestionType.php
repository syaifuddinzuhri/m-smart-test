<?php

namespace App\Enums;

use ArchTech\Enums\Values;
use ArchTech\Enums\Options;
use Filament\Support\Contracts\HasLabel;

enum QuestionType: string implements HasLabel
{
    use Options, Values;
    case SINGLE_CHOICE = 'single_choice';
    case MULTIPLE_CHOICE = 'multiple_choice';
    case TRUE_FALSE = 'true_false';
    case SHORT_ANSWER = 'short_answer';
    case ESSAY = 'essay';

    public function getLabel(): string
    {
        return match ($this) {
            self::SINGLE_CHOICE => 'Pilihan Ganda (Satu Jawaban)',
            self::MULTIPLE_CHOICE => 'Pilihan Ganda (Banyak Jawaban)',
            self::TRUE_FALSE => 'Benar / Salah',
            self::SHORT_ANSWER => 'Isian Singkat',
            self::ESSAY => 'Essay / Uraian',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->getLabel()
        ])->toArray();
    }
}
