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
        Schema::create('service_offer_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_offer_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // null pour les visiteurs non connectés
            $table->string('session_id')->nullable(); // pour les visiteurs non connectés
            $table->ipAddress('ip_address')->nullable(); // adresse IP pour tracking
            $table->string('user_agent')->nullable(); // user agent pour éviter les bots
            $table->timestamps();

            // Index pour optimiser les requêtes
            $table->index(['service_offer_id', 'created_at']);
            $table->index(['user_id', 'service_offer_id']);
            $table->index(['session_id', 'service_offer_id']);

            // Contrainte unique pour éviter les doublons par session/utilisateur
            $table->unique(['service_offer_id', 'user_id', 'session_id'], 'unique_service_offer_view');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_offer_views');
    }
};
