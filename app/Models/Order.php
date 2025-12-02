<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'total_amount',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    /**
     * Relationship: An order belongs to one user
     * This allows us to access the customer using $order->user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: An order has many order items
     * This allows us to access all items in the order using $order->items
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Update order status
     * 
     * @param string $status New status value
     * @return bool Success status
     */
    public function updateStatus($status)
    {
        $this->status = $status;
        return $this->save();
    }

    /**
     * Check if order is paid
     * 
     * @return bool True if order status is 'paid' 
     */
    public function isPaid()
    {
        return in_array($this->status, ['paid', 'processing', 'shipped', 'delivered']);
    }
}