<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Ajoute des colonnes spécifiques pour les limites des plans:
     * - max_services: Limite du nombre de services
     * - max_open_offers: Limite du nombre d'offres ouvertes
     * - max_applications: Limite du nombre de candidatures
     * - max_messages: Limite du nombre de messages
     */
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans', 'max_services')) {
                $table->integer('max_services')->nullable()->default(null)->after('limits');
            }
            if (!Schema::hasColumn('plans', 'max_open_offers')) {
                $table->integer('max_open_offers')->nullable()->default(null)->after('max_services');
            }
            if (!Schema::hasColumn('plans', 'max_applications')) {
                $table->integer('max_applications')->nullable()->default(null)->after('max_open_offers');
            }
            if (!Schema::hasColumn('plans', 'max_messages')) {
                $table->integer('max_messages')->nullable()->default(null)->after('max_applications');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'max_services',
                'max_open_offers',
                'max_applications',
                'max_messages',
            ]);
        });
    }
};

