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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('symbol', 10); // e.g., 'BTC', 'ETH'
            $table->enum('side', ['buy', 'sell']); // Order side
            $table->decimal('price', 20, 8); // Limit price
            $table->decimal('amount', 20, 8); // Order amount
            $table->decimal('filled_amount', 20, 8)->default(0); // Amount already filled
            $table->tinyInteger('status')->default(1); // 1=open, 2=filled, 3=cancelled
            $table->timestamps();

            // Indexes for efficient order book queries
            $table->index(['symbol', 'side', 'status', 'price']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

