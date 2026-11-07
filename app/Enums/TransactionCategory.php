<?php

namespace App\Enums;

/**
 * Catégories des écritures saisies à la main.
 *
 * Les ventes d'œufs et les achats d'intrants (provende, vaccins, médicaments)
 * sont repris automatiquement : ils n'ont donc pas de catégorie ici.
 */
enum TransactionCategory: string
{
    case Birds = 'birds';
    case Labor = 'labor';
    case Utilities = 'utilities';
    case Equipment = 'equipment';
    case Veterinary = 'veterinary';
    case Packaging = 'packaging';
    case Transport = 'transport';
    case Rent = 'rent';
    case OtherExpense = 'other_expense';

    case BirdSale = 'bird_sale';
    case Manure = 'manure';
    case Subsidy = 'subsidy';
    case OtherIncome = 'other_income';

    public function label(): string
    {
        return match ($this) {
            self::Birds => 'Achat de sujets (poussins, poulettes)',
            self::Labor => 'Main d\'œuvre / salaires',
            self::Utilities => 'Eau & électricité',
            self::Equipment => 'Matériel & équipement',
            self::Veterinary => 'Vétérinaire / prestations',
            self::Packaging => 'Plateaux & emballages',
            self::Transport => 'Transport',
            self::Rent => 'Loyer',
            self::OtherExpense => 'Autre dépense',
            self::BirdSale => 'Vente de poules (réformes)',
            self::Manure => 'Vente de fientes / fumier',
            self::Subsidy => 'Subvention / aide',
            self::OtherIncome => 'Autre recette',
        };
    }

    public function type(): TransactionType
    {
        return match ($this) {
            self::BirdSale, self::Manure, self::Subsidy, self::OtherIncome => TransactionType::Income,
            default => TransactionType::Expense,
        };
    }

    /**
     * @return list<self>
     */
    public static function forType(TransactionType $type): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $category) => $category->type() === $type
        ));
    }
}
