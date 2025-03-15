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
        Schema::create('imag_products', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('product_id') // Foreign key referencing products table
                  ->constrained()
                  ->cascadeOnDelete();
            $table->string('imag'); // Path to the image
            $table->timestamps(); // Cre
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imag_products');
    }
};
