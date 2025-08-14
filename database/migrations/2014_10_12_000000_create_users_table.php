<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable(); // Ajout du champ first_name
            $table->string('last_name')->nullable();  // Ajout du champ last_name
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('stripe_customer_id')->nullable();
            $table->boolean('is_professional')->default(false); // Ajout du champ is_professional, par défaut false
            $table->boolean('profile_completed')->default(false); // Ajout du champ profile_completed, par défaut false
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
