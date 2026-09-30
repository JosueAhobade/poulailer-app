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
       Schema::create('daily_reports', function (Blueprint $table) {
        $table->id();

        $table->date('report_date')->unique();

        $table->unsignedInteger('birds_count');
        $table->unsignedInteger('deaths_count')->default(0);
        $table->unsignedInteger('sick_count')->default(0);

        $table->decimal('feed_quantity', 8, 2)->nullable();
        $table->decimal('water_quantity', 8, 2)->nullable();

        $table->decimal('temperature', 5, 2)->nullable();

        $table->decimal('birds_weight', 8, 2)->nullable();

        $table->text('observations')->nullable();

        $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
