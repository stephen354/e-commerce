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
        Schema::create('cart', function (Blueprint $table) {
            $table->id();
            $table->integer('quantity')->nullable(false);
            $table->unsignedBigInteger('customer_id')->nullable(false);
            $table->unsignedBigInteger('product_id')->nullable(false);
            $table->timestamps();

            $table->foreign('customer_id')->on('customer')->references('id');
            $table->foreign('product_id')->on('product')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart');
    }
};
