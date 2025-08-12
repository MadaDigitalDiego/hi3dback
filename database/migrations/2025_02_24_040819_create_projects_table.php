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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experience_id')->constrained()->onDelete('cascade'); // Relation avec l'expérience
            $table->string('name'); // Nom du projet
            $table->text('description')->nullable();
            $table->string('image_path')->nullable(); // Chemin vers l'image du projet (upload)
            $table->string('project_url')->nullable(); // URL du projet (lien externe)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
