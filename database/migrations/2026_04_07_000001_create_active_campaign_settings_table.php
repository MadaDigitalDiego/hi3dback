<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('active_campaign_settings', function (Blueprint $table) {
            $table->id();
            $table->string('api_url')->nullable();
            $table->text('api_key')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->json('mapping')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('active_campaign_settings');
    }
};
