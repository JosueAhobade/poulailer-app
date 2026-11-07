<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seul compte administrateur au lancement des rôles.
     */
    private const ADMIN_EMAIL = 'josue@poulailler.test';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')->update(['role' => 1]);

        DB::table('users')
            ->where('email', self::ADMIN_EMAIL)
            ->update(['role' => 2]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->update(['role' => 1]);
    }
};
