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
        Schema::table('professional_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('professional_profiles', 'cover_photo')) {
                $table->string('cover_photo')->nullable()->after('avatar');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('professional_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('professional_profiles', 'cover_photo')) {
                $table->dropColumn('cover_photo');
            }
        });
    }
};
