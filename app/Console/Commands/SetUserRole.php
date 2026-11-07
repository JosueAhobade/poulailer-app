<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:set-user-role {email : Adresse e-mail du compte} {role : simple ou admin}')]
#[Description('Attribue le rôle simple (1) ou administrateur (2) à un utilisateur')]
class SetUserRole extends Command
{
    public function handle(): int
    {
        $role = match (strtolower((string) $this->argument('role'))) {
            'simple', '1' => UserRole::Simple,
            'admin', '2' => UserRole::Admin,
            default => null,
        };

        if ($role === null) {
            $this->error('Rôle inconnu : utilisez « simple » (1) ou « admin » (2).');

            return self::INVALID;
        }

        $user = User::where('email', $this->argument('email'))->first();

        if ($user === null) {
            $this->error('Aucun utilisateur avec cette adresse. Comptes existants :');
            $this->table(
                ['E-mail', 'Nom', 'Rôle'],
                User::orderBy('email')->get()->map(fn (User $user) => [
                    $user->email,
                    $user->name,
                    $user->role?->label() ?? '—',
                ])
            );

            return self::FAILURE;
        }

        $user->forceFill(['role' => $role])->save();

        $this->info("{$user->name} ({$user->email}) est maintenant : {$role->label()}.");

        return self::SUCCESS;
    }
}
