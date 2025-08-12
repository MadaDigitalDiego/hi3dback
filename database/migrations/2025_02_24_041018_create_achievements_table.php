<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_profile_id')->constrained()->onDelete('cascade'); // Relation avec le profil freelance
            $table->string('title'); // Titre de la réalisation/certification
            $table->string('organization')->nullable(); // Organisme délivrant (si applicable)
            $table->date('date_obtained')->nullable(); // Date d'obtention
            $table->text('description')->nullable();
            $table->string('file_path')->nullable(); // Chemin vers le fichier de preuve (upload)
            $table->string('achievement_url')->nullable(); // URL de la réalisation/certification (lien externe)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
