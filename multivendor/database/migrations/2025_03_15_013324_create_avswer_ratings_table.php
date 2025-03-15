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
        Schema::create('avswer_ratings', function (Blueprint $table) {
                $table->id(); // Primary key
                $table->foreignId('rating_id') // Foreign key referencing ratings table
                      ->constrained('ratings')
                      ->cascadeOnDelete();
                $table->foreignId('user_id') // Foreign key referencing users table
                      ->constrained()
                      ->cascadeOnDelete();
                $table->text('comment')->nullable(); // Comment for the answer rating
                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avswer_ratings');
    }
};
