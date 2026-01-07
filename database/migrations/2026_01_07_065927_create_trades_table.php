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
        Schema::create('trades', function (Blueprint $table) {
            $table->id();

            $table->foreignId('buy_order_id')->constrained('orders');
            $table->foreignId('sell_order_id')->constrained('orders');

            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('seller_id');

            $table->string('symbol');
            $table->decimal('price', 20, 8);
            $table->decimal('amount', 20, 8);
            $table->decimal('volume', 20, 8);
            $table->decimal('fee', 20, 8);

            $table->timestamps();

            $table->index(['symbol', 'created_at']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
