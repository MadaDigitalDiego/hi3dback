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
        Schema::create('freelance_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Clé étrangère vers la table users
            $table->string('first_name')->nullable(); // Données personnelles
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('identity_document_path')->nullable(); // KYC & Certification
            $table->string('identity_document_number')->nullable();
            $table->integer('experience')->nullable(); // Expériences & Portfolio (années d'expérience)
            $table->string('portfolio_url')->nullable();
            $table->text('education')->nullable(); // Formations & Diplômes
            $table->text('diplomas')->nullable();
            $table->json('skills')->nullable(); // Compétences (JSON pour tableau de compétences)
            $table->json('languages')->nullable(); // Langues (JSON pour langues et niveaux)
            $table->decimal('rating', 3, 1)->default(0);
            //$table->enum('availability_status', ['available', 'busy', 'vacation'])->nullable(); // Disponibilités
            $table->json('services_offered')->nullable(); // Offres de service (JSON pour services et tarifs)
            $table->decimal('hourly_rate', 10, 2)->nullable(); // Tarif horaire
            $table->integer('completion_percentage')->default(0); // Pourcentage de complétion
            $table->enum('availability_status', ['available', 'unavailable'])->nullable()->default('available'); // Statut de disponibilité (disponible/indisponible)
            $table->json('availability_details')->nullable(); // Pour stocker les jours/heures de disponibilité (JSON)
            $table->timestamp('estimated_response_time')->nullable(); // Dé
            $table->timestamps();

            $table->unique('user_id'); // Assurer une relation one-to-one avec l'utilisateur
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('freelance_profiles');
        Schema::enableForeignKeyConstraints();
    }
};
