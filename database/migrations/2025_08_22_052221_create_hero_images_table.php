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
        Schema::create('hero_images', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable()->comment('Titre de l\'image (optionnel)');
            $table->string('image_path')->comment('Chemin vers l\'image principale');
            $table->string('thumbnail_path')->nullable()->comment('Chemin vers la miniature');
            $table->boolean('is_active')->default(false)->comment('Image activée pour affichage');
            $table->integer('position')->default(0)->comment('Position pour l\'ordre d\'affichage');
            $table->string('alt_text')->nullable()->comment('Texte alternatif pour l\'accessibilité');
            $table->text('description')->nullable()->comment('Description de l\'image');
            $table->timestamps();

            // Index pour optimiser les requêtes
            $table->index(['is_active', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hero_images');
    }
};
