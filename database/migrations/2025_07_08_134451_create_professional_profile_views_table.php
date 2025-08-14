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
        Schema::create('professional_profile_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // null pour les visiteurs non connectés
            $table->string('session_id')->nullable(); // pour les visiteurs non connectés
            $table->ipAddress('ip_address')->nullable(); // adresse IP pour tracking
            $table->string('user_agent')->nullable(); // user agent pour éviter les bots
            $table->timestamps();

            // Index pour optimiser les requêtes
            $table->index(['professional_profile_id', 'created_at']);
            $table->index(['user_id', 'professional_profile_id']);
            $table->index(['session_id', 'professional_profile_id']);

            // Contrainte unique pour éviter les doublons par session/utilisateur
            $table->unique(['professional_profile_id', 'user_id', 'session_id'], 'unique_profile_view');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professional_profile_views');
    }
};
