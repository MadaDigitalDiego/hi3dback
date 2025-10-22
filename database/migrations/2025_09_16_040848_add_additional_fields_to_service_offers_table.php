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
        Schema::table('service_offers', function (Blueprint $table) {
            // These columns already exist in the original migration
            // No additional columns need to be added
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_offers', function (Blueprint $table) {
            // These columns were part of the original migration
            // No columns need to be dropped
        });
    }
};
