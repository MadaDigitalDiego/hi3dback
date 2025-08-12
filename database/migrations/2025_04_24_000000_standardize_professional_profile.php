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
        // Standardiser la table professional_profiles
        Schema::table('professional_profiles', function (Blueprint $table) {
            // Ajouter les champs manquants
            if (!Schema::hasColumn('professional_profiles', 'first_name')) {
                $table->string('first_name')->nullable();
            }
            if (!Schema::hasColumn('professional_profiles', 'last_name')) {
                $table->string('last_name')->nullable();
            }
            if (!Schema::hasColumn('professional_profiles', 'email')) {
                $table->string('email')->nullable();
            }
            if (!Schema::hasColumn('professional_profiles', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (!Schema::hasColumn('professional_profiles', 'address')) {
                $table->string('address')->nullable();
            }
            if (!Schema::hasColumn('professional_profiles', 'city')) {
                $table->string('city')->nullable();
            }
            if (!Schema::hasColumn('professional_profiles', 'country')) {
                $table->string('country')->nullable();
            }
            if (!Schema::hasColumn('professional_profiles', 'bio')) {
                $table->text('bio')->nullable();
            }
            if (!Schema::hasColumn('professional_profiles', 'avatar')) {
                $table->string('avatar')->nullable();
            }
            if (!Schema::hasColumn('professional_profiles', 'title')) {
                $table->string('title')->nullable();
            }
            if (!Schema::hasColumn('professional_profiles', 'skills')) {
                $table->json('skills')->nullable();
            }
            if (!Schema::hasColumn('professional_profiles', 'portfolio')) {
                $table->json('portfolio')->nullable();
            }
            if (!Schema::hasColumn('professional_profiles', 'availability_status')) {
                $table->string('availability_status')->default('available');
            }
            if (!Schema::hasColumn('professional_profiles', 'languages')) {
                $table->json('languages')->nullable();
            }
            if (!Schema::hasColumn('professional_profiles', 'services_offered')) {
                $table->json('services_offered')->nullable();
            }
            if (!Schema::hasColumn('professional_profiles', 'rating')) {
                $table->decimal('rating', 3, 1)->default(0);
            }
            if (!Schema::hasColumn('professional_profiles', 'social_links')) {
                $table->json('social_links')->nullable();
            }
            if (!Schema::hasColumn('professional_profiles', 'completion_percentage')) {
                $table->integer('completion_percentage')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('professional_profiles', function (Blueprint $table) {
            // Supprimer les champs ajoutés
            $table->dropColumn([
                'first_name',
                'last_name',
                'email',
                'phone',
                'address',
                'city',
                'country',
                'bio',
                'avatar',
                'title',
                'skills',
                'portfolio',
                'availability_status',
                'languages',
                'services_offered',
                'rating',
                'social_links',
                'completion_percentage'
            ]);
        });
    }
};
