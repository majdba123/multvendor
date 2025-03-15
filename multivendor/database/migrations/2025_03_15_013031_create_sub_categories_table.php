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
        Schema::create('sub_categories', function (Blueprint $table) {
                $table->id(); // Primary key
                $table->foreignId('category_id') // Foreign key referencing categories table
                      ->constrained()
                      ->cascadeOnDelete();
                $table->string('name'); // Name of the subcategory
                $table->string('imag')->nullable(); // Image path for the subcategory (optional)
                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_categories');
    }
};
