<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::table('daily_reports', function (Blueprint $table) {

            $table->decimal('feed_consumed', 8, 2)
                ->nullable()
                ->after('feed_quantity');

            $table->decimal('water_consumed', 8, 2)
                ->nullable()
                ->after('water_quantity');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropColumn([
                'feed_consumed',
                'water_consumed',
            ]);
        });
    }
};
