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
            $table->foreignId('feed_input_id')
                ->nullable()
                ->after('feed_quantity')
                ->constrained('inputs')
                ->nullOnDelete();
        });

        Schema::table('treatments', function (Blueprint $table) {
            $table->foreignId('input_id')
                ->nullable()
                ->after('daily_report_id')
                ->constrained('inputs')
                ->nullOnDelete();

            $table->decimal('quantity_used', 10, 2)
                ->nullable()
                ->after('dosage');
        });

        Schema::table('vaccinations', function (Blueprint $table) {
            $table->foreignId('input_id')
                ->nullable()
                ->after('daily_report_id')
                ->constrained('inputs')
                ->nullOnDelete();

            $table->decimal('quantity_used', 10, 2)
                ->nullable()
                ->after('dosage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaccinations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('input_id');
            $table->dropColumn('quantity_used');
        });

        Schema::table('treatments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('input_id');
            $table->dropColumn('quantity_used');
        });

        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('feed_input_id');
        });
    }
};
