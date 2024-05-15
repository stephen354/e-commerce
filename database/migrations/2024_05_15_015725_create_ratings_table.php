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
        Schema::create('rating', function (Blueprint $table) {
            $table->id();
            $table->integer('rate')->nullable(false);
            $table->unsignedBigInteger('customer_id')->nullable(false);
            $table->unsignedBigInteger('order_id')->nullable(false);
            $table->timestamps();

            $table->foreign('customer_id')->on('customer')->references('id');
            $table->foreign('order_id')->on('order')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rating');
    }
};
