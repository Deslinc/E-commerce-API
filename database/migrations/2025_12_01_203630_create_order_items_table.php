<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates order_items table - stores individual products in each order
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            // Primary key
            $table->id();
            
            // Foreign key to orders table
            // Links this item to a specific order
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            // Foreign key to products table
            // Links this item to the product that was ordered
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Quantity of this product in the order
            $table->integer('quantity');
            
            // Price at the time of purchase
            // storing this because product prices might change over time
            // This preserves the historical price for this order
            $table->decimal('price', 10, 2);
            
            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};