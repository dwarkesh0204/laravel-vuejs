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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('symbol', 10); // e.g., 'BTC', 'ETH'
            $table->decimal('amount', 20, 8)->default(0); // Available amount
            $table->decimal('locked_amount', 20, 8)->default(0); // Reserved for open sell orders
            $table->timestamps();

            // Each user can only have one record per symbol
            $table->unique(['user_id', 'symbol']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};

