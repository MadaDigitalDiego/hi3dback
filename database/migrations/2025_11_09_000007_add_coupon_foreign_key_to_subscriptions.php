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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Ajouter la clé étrangère vers coupons si elle n'existe pas
            if (Schema::hasColumn('subscriptions', 'coupon_id') && 
                !Schema::hasColumn('subscriptions', 'coupon_id_foreign')) {
                try {
                    $table->foreign('coupon_id')
                        ->references('id')
                        ->on('coupons')
                        ->onDelete('set null');
                } catch (\Exception $e) {
                    // La clé étrangère existe peut-être déjà
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            try {
                $table->dropForeign(['coupon_id']);
            } catch (\Exception $e) {
                // La clé étrangère n'existe peut-être pas
            }
        });
    }
};

