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
                $table->id(); // Primary key
                $table->foreignId('sub_category_id') // Foreign key referencing sub_categories table
                      ->constrained()
                      ->cascadeOnDelete();

                $table->foreignId('vendor_id') // Foreign key referencing sub_categories table
                            ->constrained()
                            ->cascadeOnDelete();
                $table->string('name'); // Name of the product
                $table->text('discription')->nullable(); // Description of the product
                $table->decimal('price', 10, 2); // Price of the product
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
