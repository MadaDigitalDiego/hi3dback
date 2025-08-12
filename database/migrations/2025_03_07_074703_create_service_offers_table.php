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
        Schema::create('service_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Professional offering the service
            $table->string('title');
            $table->text('description');
            $table->json('categories')->nullable(); // Categories for the service
            $table->decimal('price', 10, 2); // Price of the service
            $table->string('execution_time')->nullable(); // Estimated execution time (renamed from delivery_time)
            $table->json('files')->nullable(); // Images, videos, documents (URLs, etc.) - will include imageUrl
            $table->string('concepts')->nullable(); // New field: Concepts
            $table->string('revisions')->nullable(); // New field: Revisions
            $table->boolean('is_private')->default(false); // New field: Is Private
            $table->integer('likes')->default(0); // New field: Likes
            $table->integer('views')->default(0); // New field: Views (renamed from views_count)
            $table->decimal('rating', 3, 1)->default(0);
            $table->enum('status', ['draft', 'published', 'pending'])->default('draft'); // Status
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_offers');
    }
};
