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
        Schema::create('professional_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->unique()->constrained()->onDelete('cascade');
            $table->string('title')->nullable();
            $table->string('profession')->nullable()->default('Non spécifié');
            $table->json('expertise')->nullable();
            $table->integer('years_of_experience')->nullable()->default(0);
            $table->decimal('hourly_rate', 10, 2)->nullable()->default(0.00);
            $table->text('description')->nullable();
            $table->json('skills')->nullable();
            $table->json('portfolio')->nullable();
            $table->enum('availability_status', ['available', 'unavailable', 'busy'])->default('unavailable');
            $table->json('languages')->nullable();
            $table->json('services_offered')->nullable();
            $table->decimal('rating', 3, 1)->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professional_details');
    }
};
