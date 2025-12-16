<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('open_offers', function (Blueprint $table) {
            $table->json('attachment_links')->nullable()->after('files');
        });
    }

    public function down(): void
    {
        Schema::table('open_offers', function (Blueprint $table) {
            $table->dropColumn('attachment_links');
        });
    }
};

