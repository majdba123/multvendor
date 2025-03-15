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
        Schema::create('afiliate_products', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('afiliate_id') // Foreign key referencing afiliates table
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('product_id') // Foreign key referencing products table
                  ->constrained()
                  ->cascadeOnDelete();
            $table->string('status')->default('active'); // Status of the affiliate-product relationship
            $table->string('countpayment')->default(0); // Status of the affiliate-product relationship
            $table->decimal('profit', 10, 2); // Profit percentage or fixed amount
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('afiliate_products');
    }
};
