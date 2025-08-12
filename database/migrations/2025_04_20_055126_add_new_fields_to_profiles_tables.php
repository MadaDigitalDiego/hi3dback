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
        // Ajouter les nouveaux champs à la table freelance_profiles
        Schema::table('freelance_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('freelance_profiles', 'bio')) {
                $table->text('bio')->nullable();
            }
            if (!Schema::hasColumn('freelance_profiles', 'avatar')) {
                $table->string('avatar')->nullable();
            }
            if (!Schema::hasColumn('freelance_profiles', 'portfolio')) {
                $table->json('portfolio')->nullable();
            }
            if (!Schema::hasColumn('freelance_profiles', 'rating')) {
                $table->decimal('rating', 3, 1)->nullable()->default(0);
            }
            if (!Schema::hasColumn('freelance_profiles', 'title')) {
                $table->string('title')->nullable();
            }
        });

        // Ajouter les nouveaux champs à la table company_profiles
        Schema::table('company_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('company_profiles', 'first_name')) {
                $table->string('first_name')->nullable();
            }
            if (!Schema::hasColumn('company_profiles', 'last_name')) {
                $table->string('last_name')->nullable();
            }
            if (!Schema::hasColumn('company_profiles', 'bio')) {
                $table->text('bio')->nullable();
            }
            if (!Schema::hasColumn('company_profiles', 'avatar')) {
                $table->string('avatar')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les champs ajoutés à la table freelance_profiles
        Schema::table('freelance_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'bio',
                'avatar',
                'portfolio',
                'rating',
                'title'
            ]);
        });

        // Supprimer les champs ajoutés à la table company_profiles
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'bio',
                'avatar'
            ]);
        });
    }
};
