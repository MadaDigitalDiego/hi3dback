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
            $table->json('youtube_links')->nullable()->after('youtube_link');
        });

        Schema::table('achievements', function (Blueprint $table) {
            $table->json('youtube_links')->nullable()->after('youtube_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_offers', function (Blueprint $table) {
            $table->dropColumn('youtube_links');
        });

        Schema::table('achievements', function (Blueprint $table) {
            $table->dropColumn('youtube_links');
        });
    }
};
