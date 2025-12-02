<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Allows us to use create() and update() with these fields
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'image_url',
    ];

    /**
     * The attributes that should be cast to native types.
     * Ensures price is always a decimal and stock is always an integer
     */
    protected $casts = [
        'price' => 'decimal:2', // Always 2 decimal places
        'stock' => 'integer',
    ];

    /**
     * Relationship: A product can be in many cart items
     * This allows us to see which carts contain this product
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Relationship: A product can be in many order items
     * This allows us to see all orders that include this product
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Check if product has sufficient stock
     * 
     * @param int $quantity The quantity to check
     * @return bool True if stock is sufficient
     */
    public function hasStock($quantity)
    {
        return $this->stock >= $quantity;
    }

    /**
     * Reduce product stock
     * 
     * @param int $quantity Amount to reduce
     * @return bool Success status
     */
    public function reduceStock($quantity)
    {
        if ($this->hasStock($quantity)) {
            $this->stock -= $quantity;
            return $this->save();
        }
        return false;
    }

    /**
     * Increase product stock (for restocking)
     * 
     * @param int $quantity Amount to add
     * @return bool Success status
     */
    public function increaseStock($quantity)
    {
        $this->stock += $quantity;
        return $this->save();
    }
}