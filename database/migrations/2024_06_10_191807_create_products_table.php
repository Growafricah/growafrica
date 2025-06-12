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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->constrained('categories', 'id');
            $table->foreignUuid('seller_id')->constrained('users', 'id');
            $table->string('name');
            $table->longText('description')->nullable();
            $table->longText('specification')->nullable();
            $table->string('color');

            $table->integer('sales_number')->nullable();
            $table->integer('inventory')->nullable();
            $table->double('unit_price')->nullable();
            $table->double('discount')->nullable();
            $table->double('vat')->nullable();

            $table->double('rating')->nullable();
            $table->boolean('status')->nullable()->default(true);
            $table->string('images')->nullable();
            $table->string('thumbnail')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
