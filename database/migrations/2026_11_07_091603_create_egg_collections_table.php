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
        Schema::create('egg_collections', function (Blueprint $table) {
            $table->id();

            $table->date('collection_date')->unique();

            $table->unsignedInteger('collected_count');
            $table->unsignedInteger('broken_count')->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egg_collections');
    }
};
