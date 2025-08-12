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
        Schema::create('client_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->unique()->constrained()->onDelete('cascade');
            $table->enum('type', ['particulier', 'entreprise'])->default('particulier');
            $table->string('company_name')->nullable();
            $table->string('company_size')->nullable();
            $table->string('industry')->nullable();
            $table->string('position')->nullable();
            $table->string('website')->nullable();
            $table->string('registration_number')->nullable();
            $table->date('birth_date')->nullable();
            $table->json('preferences')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_details');
    }
};
