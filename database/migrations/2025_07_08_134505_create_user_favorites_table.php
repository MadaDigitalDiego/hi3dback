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
        Schema::create('user_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('favoritable'); // permet de favoriser différents types d'objets
            $table->timestamps();

            // Index pour optimiser les requêtes
            $table->index(['user_id', 'favoritable_type', 'favoritable_id']);

            // Contrainte unique pour éviter les doublons
            $table->unique(['user_id', 'favoritable_type', 'favoritable_id'], 'unique_user_favorite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_favorites');
    }
};
