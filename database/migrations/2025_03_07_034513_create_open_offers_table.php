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
        Schema::create('open_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->json('categories')->nullable(); // Pour stocker un tableau de catégories
            $table->json('filters')->nullable(); // Pour stocker un tableau de catégories
            $table->string('budget')->nullable(); // Budget en string
            $table->timestamp('deadline')->nullable(); // Date limite
            $table->string('company')->nullable();
            $table->string('website')->nullable();
            $table->text('description');
            $table->json('files')->nullable(); // Pour stocker des informations sur les fichiers (URLs, noms, etc.)
            $table->enum('recruitment_type', ['company', 'personal'])->default('company');
            $table->boolean('open_to_applications')->default(true);
            $table->boolean('auto_invite')->default(false);
            $table->enum('status', ['pending','open','closed','in_progress','completed','invited'])->default('open');
            $table->integer('views_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('open_offers');
    }
};
