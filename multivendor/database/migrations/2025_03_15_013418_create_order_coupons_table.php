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
        Schema::create('order_coupons', function (Blueprint $table) {
                $table->id(); // Primary key
                $table->foreignId('order_id') // Foreign key referencing orders table
                      ->constrained()
                      ->cascadeOnDelete();
                $table->foreignId('coupon_id') // Foreign key referencing coupons table
                      ->constrained()
                      ->cascadeOnDelete();
                $table->decimal('value', 10, 2); // Value of the coupon's discount applied to the order
                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_coupons');
    }
};
