<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Vérifier si la table professional_profiles existe
        if (!Schema::hasTable('professional_profiles')) {
            Schema::create('professional_profiles', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }

        // Renommer la colonne freelance_profile_id en professional_profile_id
        Schema::table('achievements', function (Blueprint $table) {
            // Supprimer d'abord la contrainte de clé étrangère existante si elle existe
            $table->dropForeign(['freelance_profile_id']);

            // Renommer la colonne
            $table->renameColumn('freelance_profile_id', 'professional_profile_id');

            // Ajouter la nouvelle contrainte de clé étrangère
            $table->foreign('professional_profile_id')
                  ->references('id')
                  ->on('professional_profiles')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            // Supprimer la contrainte de clé étrangère
            $table->dropForeign(['professional_profile_id']);

            // Renommer la colonne en arrière
            $table->renameColumn('professional_profile_id', 'freelance_profile_id');

            // Restaurer l'ancienne contrainte de clé étrangère
            $table->foreign('freelance_profile_id')
                  ->references('id')
                  ->on('freelance_profiles')
                  ->onDelete('cascade');
        });
    }
};


