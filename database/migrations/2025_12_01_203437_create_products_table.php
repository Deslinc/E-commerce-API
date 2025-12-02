<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This creates the products table to store all product information
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            // Primary key - auto-incrementing ID
            $table->id();
            
            // Product name - required field
            $table->string('name');
            
            // Detailed product description 
            $table->text('description');
            
            // Product price - stored as decimal with 2 decimal places
            // Total of 10 digits, 2 after decimal point
            $table->decimal('price', 10, 2);
            
            // Stock quantity - integer, default 0
            // This tracks how many items are available
            $table->integer('stock')->default(0);
            
            // URL or path to product image - nullable in case no image is uploaded
            $table->string('image_url')->nullable();
            
            // Timestamps - automatically manages created_at and updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * This drops the products table if we rollback
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};