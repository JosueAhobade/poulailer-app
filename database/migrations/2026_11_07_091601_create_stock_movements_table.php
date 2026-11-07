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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('input_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('type');

            // Positive = entrée en stock, négative = sortie.
            $table->decimal('quantity', 10, 2);

            $table->date('movement_date');

            $table->decimal('unit_price', 12, 2)->nullable();
            $table->string('supplier')->nullable();
            $table->text('notes')->nullable();

            // Rapport, traitement ou vaccination à l'origine de la sortie.
            $table->nullableMorphs('source');

            $table->timestamps();

            $table->index(['input_id', 'movement_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
