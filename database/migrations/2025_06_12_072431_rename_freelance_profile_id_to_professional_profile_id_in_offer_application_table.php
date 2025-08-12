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
        Schema::table('offer_applications', function (Blueprint $table) {
            // Supprimer d'abord la contrainte de clé étrangère existante si elle existe
            if (Schema::hasColumn('offer_applications', 'freelance_profile_id')) {
                $table->dropForeign(['freelance_profile_id']);
                $table->renameColumn('freelance_profile_id', 'professional_profile_id');
            } else {
                $table->unsignedBigInteger('professional_profile_id')->nullable();
            }

            // Ajouter la nouvelle contrainte de clé étrangère
            $table->foreign('professional_profile_id')
                  ->references('id')
                  ->on('professional_profiles')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_applications', function (Blueprint $table) {
            // Supprimer la contrainte de clé étrangère
            $table->dropForeign(['professional_profile_id']);

            // Renommer la colonne en arrière
            $table->renameColumn('professional_profile_id', 'freelance_profile_id');

            // Restaurer l'ancienne contrainte de clé étrangère
            $table->foreign('freelance_profile_id')
                  ->references('id')
                  ->on('professional_profiles')
                  ->onDelete('cascade');
        });
    }
};
