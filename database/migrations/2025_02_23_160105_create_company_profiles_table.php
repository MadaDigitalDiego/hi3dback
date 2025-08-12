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
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Clé étrangère vers la table users
            $table->string('company_name')->nullable(); // Données personnelles (Entreprise)
            $table->string('company_size')->nullable();
            $table->string('industry')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('registration_number')->nullable(); // KYC & Certification (Numéro d'enregistrement entreprise)
            $table->text('description')->nullable(); // Description de l'entreprise
            $table->integer('completion_percentage')->default(0); // Pourcentage de complétion
            $table->timestamps();

            $table->unique('user_id'); // Assurer une relation one-to-one avec l'utilisateur
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};
