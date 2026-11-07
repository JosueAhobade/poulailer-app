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
        Schema::create('egg_sale_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('egg_sale_id')
                ->constrained()
                ->cascadeOnDelete();

            // Une collecte déjà vendue ne peut pas être supprimée.
            $table->foreignId('egg_collection_id')
                ->constrained()
                ->restrictOnDelete();

            $table->unsignedInteger('quantity');

            $table->timestamps();

            $table->unique(['egg_sale_id', 'egg_collection_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egg_sale_items');
    }
};
