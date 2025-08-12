<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('name');
            $table->text('description');
            $table->string('stripe_product_id');
            $table->string('stripe_price_id');
            $table->decimal('price', 10, 2);
            $table->string('interval')->default('month'); // month, year
            $table->integer('interval_count')->default(1);
            $table->boolean('is_active')->default(true);
            $table->json('features')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('plans');
    }
};
