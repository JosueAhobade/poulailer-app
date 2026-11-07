<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Purchase = 'purchase';
    case Consumption = 'consumption';
    case Loss = 'loss';
    case Adjustment = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Achat / entrée',
            self::Consumption => 'Consommation',
            self::Loss => 'Perte / périmé',
            self::Adjustment => 'Ajustement inventaire',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Purchase => 'bg-green-lt',
            self::Consumption => 'bg-blue-lt',
            self::Loss => 'bg-red-lt',
            self::Adjustment => 'bg-yellow-lt',
        };
    }

    /**
     * Types qu'on peut saisir à la main (la consommation vient des rapports).
     *
     * @return list<self>
     */
    public static function manual(): array
    {
        return [self::Purchase, self::Loss, self::Adjustment];
    }
}
