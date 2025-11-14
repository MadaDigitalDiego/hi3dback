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
            // Ajouter les colonnes manquantes si elles n'existent pas
            if (!Schema::hasColumn('plans', 'stripe_price_id_monthly')) {
                $table->string('stripe_price_id_monthly')->nullable()->after('stripe_price_id');
            }
            if (!Schema::hasColumn('plans', 'stripe_price_id_yearly')) {
                $table->string('stripe_price_id_yearly')->nullable()->after('stripe_price_id_monthly');
            }
            if (!Schema::hasColumn('plans', 'limits')) {
                $table->json('limits')->nullable()->after('features');
            }
            if (!Schema::hasColumn('plans', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('limits');
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
                'stripe_price_id_monthly',
                'stripe_price_id_yearly',
                'limits',
                'sort_order',
            ]);
        });
    }
};

