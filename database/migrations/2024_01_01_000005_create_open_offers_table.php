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
            $table->json('categories')->nullable();
            $table->json('filters')->nullable();
            $table->string('budget')->nullable();
            $table->date('deadline')->nullable();
            $table->string('company')->nullable();
            $table->string('website')->nullable();
            $table->text('description');
            $table->json('files')->nullable();
            $table->string('recruitment_type')->nullable();
            $table->boolean('open_to_applications')->default(true);
            $table->boolean('auto_invite')->default(false);
            $table->string('status')->default('active');
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
