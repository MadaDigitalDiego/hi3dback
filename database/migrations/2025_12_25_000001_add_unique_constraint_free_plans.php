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
            // Add unique constraint for free plans per user type
            // This ensures only one free plan (price = 0) can exist per user type
            $table->unique(['user_type', 'price'], 'unique_free_plan_per_user_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropUnique('unique_free_plan_per_user_type');
        });
    }
};
