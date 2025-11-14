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
        Schema::table('plans', function (Blueprint $table) {
            // Ajouter le champ user_type pour distinguer les plans par type d'utilisateur
            if (!Schema::hasColumn('plans', 'user_type')) {
                $table->enum('user_type', ['professional', 'client'])->default('professional')->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'user_type')) {
                $table->dropColumn('user_type');
            }
        });
    }
};

