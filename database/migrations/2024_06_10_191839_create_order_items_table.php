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
        Schema::create('order_items', function (Blueprint $table) {
            
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders', 'id');
            $table->foreignUuid('product_id')->constrained('products', 'id');
            $table->integer('quantity');
            $table->double('unit_price');
            $table->double('total');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
