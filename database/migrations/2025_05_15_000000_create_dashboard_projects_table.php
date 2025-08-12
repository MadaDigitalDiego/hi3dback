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
        if (!Schema::hasTable('dashboard_projects')) {
            Schema::create('dashboard_projects', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('title');
                $table->text('description');
                $table->string('category');
                $table->string('budget');
                $table->date('deadline');
                $table->json('skills')->nullable();
                $table->json('attachments')->nullable();
                $table->enum('status', ['draft', 'open', 'in_progress', 'completed', 'cancelled'])->default('draft');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_projects');
    }
};
