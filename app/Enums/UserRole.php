<?php

namespace App\Enums;

enum UserRole: int
{
    case Simple = 1;
    case Admin = 2;

    public function label(): string
    {
        return match ($this) {
            self::Simple => 'Utilisateur',
            self::Admin => 'Administrateur',
        };
    }
}
