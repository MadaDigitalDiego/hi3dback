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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 8, 2);
            $table->string('price_unit')->default('par projet');
            $table->string('execution_time')->nullable();
            $table->string('associated_project')->nullable();
            $table->integer('concepts')->nullable();
            $table->integer('revisions')->nullable();
            $table->boolean('is_private')->default(false);
            $table->string('status')->default('active');
            $table->json('categories')->nullable();
            $table->json('files')->nullable();
            $table->integer('views')->default(0);
            $table->integer('likes')->default(0);
            $table->decimal('rating', 3, 1)->nullable();
            $table->string('image')->nullable();
            $table->text('what_you_get')->nullable();
            $table->text('who_is_this_for')->nullable();
            $table->text('delivery_method')->nullable();
            $table->text('why_choose_me')->nullable();
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
