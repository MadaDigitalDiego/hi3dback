<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Crée la table stripe_configurations pour stocker les configurations Stripe
     * centralisées dans le back office
     */
    public function up(): void
    {
        Schema::create('stripe_configurations', function (Blueprint $table) {
            $table->id();
            
            // Clés Stripe
            $table->text('public_key')->nullable(); // Clé publique (pk_test_...)
            $table->text('secret_key')->nullable(); // Clé secrète (sk_test_...)
            $table->text('webhook_secret')->nullable(); // Secret webhook (whsec_...)
            
            // Mode Stripe
            $table->enum('mode', ['test', 'live'])->default('test');
            
            // Statut
            $table->boolean('is_active')->default(true);
            
            // Métadonnées
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            
            // Audit
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_configurations');
    }
};

