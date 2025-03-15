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
        Schema::create('order_products', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('order_id') // Foreign key referencing orders table
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('product_id') // Foreign key referencing products table
                  ->constrained()
                  ->cascadeOnDelete();
            $table->integer('quantity')->default(1); // Quantity of the product in the order
            $table->decimal('total_price', 10, 2); // Total price for this product in the order
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_products');
    }
};
