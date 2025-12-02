<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    /**
     * Relationship: An order item belongs to one order
     * This allows access to the parent order using $orderItem->order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship: An order item belongs to one product
     * This allows us to access product details using $orderItem->product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate the subtotal for this order item
     * Subtotal = quantity × price (price at time of purchase)
     * 
     * @return float Subtotal amount
     */
    public function getSubtotal()
    {
        return $this->quantity * $this->price;
    }
}