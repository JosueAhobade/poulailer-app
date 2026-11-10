<?php

namespace App\Enums;

enum InputCategory: string
{
    case Feed = 'feed';
    case Medicine = 'medicine';

    public function label(): string
    {
        return match ($this) {
            self::Feed => 'Provende',
            self::Medicine => 'Médicament',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Feed => '🌽',
            self::Medicine => '💊',
        };
    }

    /**
     * Unités proposées dans le formulaire pour cette catégorie.
     *
     * @return list<string>
     */
    public function suggestedUnits(): array
    {
        return match ($this) {
            self::Feed => ['kg'],
            self::Medicine => ['g', 'kg', 'ml', 'L', 'sachet', 'flacon'],
        };
    }
}
