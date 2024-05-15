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
        Schema::create('payment', function (Blueprint $table) {
            $table->id();
            $table->date('payment_date')->nullable(false);
            $table->double('amount')->nullable(false);
            $table->string('token', 100)->nullable()->unique('payment_token_unique');
            $table->string('status', 50);
            $table->unsignedBigInteger('customer_id')->nullable(false);
            $table->timestamps();

            $table->foreign('customer_id')->on('customer')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment');
    }
};
