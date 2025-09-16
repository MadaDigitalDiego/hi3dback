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
        Schema::create('gmail_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Gmail OAuth Configuration');
            $table->string('client_id');
            $table->text('client_secret'); // Crypté
            $table->string('redirect_uri');
            $table->json('scopes')->default('["openid", "profile", "email"]');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gmail_configurations');
    }
};
