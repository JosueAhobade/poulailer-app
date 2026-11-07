<?php

namespace App\Enums;

enum InputCategory: string
{
    case Feed = 'feed';
    case Vaccine = 'vaccine';
    case Medicine = 'medicine';

    public function label(): string
    {
        return match ($this) {
            self::Feed => 'Provende',
            self::Vaccine => 'Vaccin',
            self::Medicine => 'Médicament',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Feed => '🌽',
            self::Vaccine => '💉',
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
            self::Vaccine => ['doses', 'flacon', 'ml'],
            self::Medicine => ['g', 'kg', 'ml', 'L', 'sachet', 'flacon'],
        };
    }
}
