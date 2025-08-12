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
        // Standardiser la table client_profiles
        Schema::table('client_profiles', function (Blueprint $table) {
            // S'assurer que tous les champs nécessaires existent
            if (!Schema::hasColumn('client_profiles', 'type')) {
                $table->string('type')->default('particulier'); // 'particulier' ou 'entreprise'
            }
            if (!Schema::hasColumn('client_profiles', 'company_name')) {
                $table->string('company_name')->nullable(); // Nullable si particulier
            }
            if (!Schema::hasColumn('client_profiles', 'industry')) {
                $table->string('industry')->nullable(); // Nullable si particulier
            }
            if (!Schema::hasColumn('client_profiles', 'company_size')) {
                $table->string('company_size')->nullable(); // Nullable si particulier
            }
            if (!Schema::hasColumn('client_profiles', 'position')) {
                $table->string('position')->nullable();
            }
            if (!Schema::hasColumn('client_profiles', 'website')) {
                $table->string('website')->nullable();
            }
            if (!Schema::hasColumn('client_profiles', 'preferences')) {
                $table->json('preferences')->nullable();
            }
            if (!Schema::hasColumn('client_profiles', 'completion_percentage')) {
                $table->integer('completion_percentage')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            // Supprimer les champs ajoutés
            $table->dropColumn([
                'type',
                'company_name',
                'industry',
                'company_size',
                'position',
                'website',
                'preferences',
                'completion_percentage'
            ]);
        });
    }
};
