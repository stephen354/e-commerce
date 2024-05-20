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
        Schema::create('order', function (Blueprint $table) {
            $table->id();
            $table->integer('quantity')->nullable(false);
            $table->integer('price')->nullable(false);
            $table->unsignedBigInteger('payment_id')->nullable(false);
            $table->unsignedBigInteger('product_id')->nullable(false);
            $table->timestamps();

            $table->foreign('payment_id')->on('payment')->references('id')->onDelete('cascade');
            $table->foreign('product_id')->on('product')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order');
    }
};
