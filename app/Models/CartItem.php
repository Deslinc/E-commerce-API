<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * A cart item belongs to one cart.
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * A cart item belongs to one product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate subtotal for this cart item.
     *
     * Subtotal = quantity * product price.
     * Guard against missing product (e.g., deleted product).
     *
     * @return float
     */
    public function getSubtotal(): float
    {
        // If product is missing, treat price as 0 instead of crashing my API
        $price = optional($this->product)->price ?? 0;

        return (float) $this->quantity * (float) $price;
    }
}
