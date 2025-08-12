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
        Schema::create('open_offer_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('open_offer_id')->constrained()->cascadeOnDelete(); // Foreign key to open_offers table
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();       // Foreign key to users table
            $table->timestamps();

            // Optional: Add unique index to prevent duplicate entries for the same offer and user
            $table->unique(['open_offer_id', 'user_id'], 'unique_offer_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('open_offer_user');
    }
};
