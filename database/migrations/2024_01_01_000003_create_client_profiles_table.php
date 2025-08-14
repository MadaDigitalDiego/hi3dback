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
        Schema::create('client_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['particulier', 'entreprise'])->default('particulier');
            $table->string('company_name')->nullable(); // Nullable si particulier
            $table->string('industry')->nullable(); // Nullable si particulier
            $table->text('description')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->text('bio')->nullable();
            $table->string('avatar')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('position')->nullable();
            $table->string('company_size')->nullable();
            $table->string('website')->nullable();
            $table->json('social_links')->nullable();
            $table->json('preferences')->nullable();
            $table->integer('completion_percentage')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_profiles');
    }
};
