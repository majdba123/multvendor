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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('user_id') // Foreign key referencing the users table
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('product_id') // Foreign key referencing the products table
                  ->constrained()
                  ->cascadeOnDelete();
            $table->tinyInteger('num')->unsigned(); // Numeric rating (e.g., 1-5 stars)
            $table->text('comment')->nullable(); // Optional comment for the rating
            $table->timestamps(); // Created at and
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
