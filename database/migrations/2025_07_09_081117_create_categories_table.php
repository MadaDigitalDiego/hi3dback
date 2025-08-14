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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('value')->unique()->comment('Identifiant technique unique');
            $table->string('name')->comment('Nom affiché de la catégorie');
            $table->string('slug')->unique()->comment('Slug pour les URLs');
            $table->text('description')->nullable()->comment('Description de la catégorie');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('ID de la catégorie parente');
            $table->string('image_url')->nullable()->comment('URL de l\'image associée');
            $table->integer('count')->default(0)->comment('Nombre d\'éléments dans cette catégorie');
            $table->integer('order')->default(0)->comment('Ordre d\'affichage');
            $table->boolean('is_active')->default(true)->comment('Statut actif/inactif');
            $table->timestamps();

            // Index pour optimiser les performances
            $table->index('value');
            $table->index('slug');
            $table->index('parent_id');
            $table->index('is_active');
            $table->index(['parent_id', 'order']);

            // Contrainte de clé étrangère pour parent_id
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
