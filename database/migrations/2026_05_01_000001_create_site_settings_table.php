<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            
            // About page content
            $table->string('about_title')->nullable();
            $table->text('about_subtitle')->nullable();
            $table->longText('about_story')->nullable();
            $table->longText('about_mission')->nullable();
            $table->json('about_values')->nullable();
            $table->json('about_team')->nullable();
            $table->text('about_cta_title')->nullable();
            $table->text('about_cta_description')->nullable();
            
            // Social media links
            $table->string('social_facebook')->nullable();
            $table->string('social_twitter')->nullable();
            $table->string('social_instagram')->nullable();
            $table->string('social_linkedin')->nullable();
            $table->string('social_youtube')->nullable();
            $table->string('social_tiktok')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
