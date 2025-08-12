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
        Schema::create('professional_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('avatar')->nullable();
            $table->string('portfolio_items')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->text('bio')->nullable();
            $table->string('title')->nullable();
            $table->string('expertise')->nullable();
            $table->integer('completion_percentage')->nullable()->default(0);
            $table->string('profession')->nullable()->default('Non spécifié');
            $table->integer('years_of_experience')->nullable()->default(0);
            $table->decimal('hourly_rate', 10, 2)->nullable()->default(0.00);
            $table->text('description')->nullable();
            $table->string('availability_status')->nullable()->default('unavailable');
            $table->string('estimated_response_time')->nullable();
            $table->float('rating')->nullable()->default(0.0);
            $table->json('skills')->nullable();
            $table->json('languages')->nullable();
            $table->json('services_offered')->nullable();
            $table->json('portfolio')->nullable();
            $table->json('social_links')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professional_profiles');
    }
};