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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('user_id') // Foreign key referencing users table
                  ->constrained()
                  ->cascadeOnDelete();
            $table->decimal('lang', 10, 7)->nullable(); // Longitude (high precision for coordinates)
            $table->decimal('lat', 10, 7)->nullable(); // Latitude (high precision for coordinates)
            $table->string('image')->nullable(); // Profile image path
            $table->string('address')->nullable(); // User's
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
