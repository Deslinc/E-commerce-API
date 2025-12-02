<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
    ];

    /**
     * Relationship: A cart belongs to one user
     * This allows access to the cart owner using $cart->user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: A cart has many cart items
     * This allows us to access all items in the cart using $cart->items
     */
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Calculate the total amount for all items in the cart
     * 
     * @return float Total amount
     */
    public function calculateTotal()
    {
        // Load cart items with their associated products
        // For each item, multiply quantity by product price
        // Sum all the results
        return $this->items()->with('product')->get()->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });
    }

    /**
     * Get cart with all items and product details
     * 
     * @return array Cart data with totals
     */
    public function getCartDetails()
    {
        // Load all cart items with their product information
        $items = $this->items()->with('product')->get();
        
        // Calculate total
        $total = $this->calculateTotal();
        
        return [
            'cart_id' => $this->id,
            'items' => $items,
            'total' => $total,
            'item_count' => $items->sum('quantity'),
        ];
    }

    /**
     * Clear all items from the cart
     * Used after successful checkout
     */
    public function clearCart()
    {
        $this->items()->delete();
    }
}