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
            $table->text('what_you_get')->nullable()->after('image');
            $table->text('who_is_this_for')->nullable()->after('what_you_get');
            $table->text('delivery_method')->nullable()->after('who_is_this_for');
            $table->text('why_choose_me')->nullable()->after('delivery_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_offers', function (Blueprint $table) {
            $table->dropColumn(['what_you_get', 'who_is_this_for', 'delivery_method', 'why_choose_me']);
        });
    }
};
