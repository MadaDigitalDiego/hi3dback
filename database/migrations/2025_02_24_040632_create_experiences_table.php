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
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelance_profile_id')->constrained()->onDelete('cascade'); // Relation avec le profil freelance
            $table->string('title'); // Poste ou titre de l'expérience
            $table->string('company_name');
            $table->date('start_date');
            $table->date('end_date')->nullable(); // Peut être null si toujours en poste
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};

