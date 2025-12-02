<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    // Laravel Sanctum trait for token-based authentication
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     * These fields can be filled using create() or fill() methods
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin', // Added to allow setting admin status
    ];

    /**
     * The attributes that should be hidden for serialization.
     * These won't appear in JSON responses
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     * Automatically converts these to proper data types
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // Automatically hashes password
            'is_admin' => 'boolean', // Converts is_admin to boolean
        ];
    }

    /**
     * Relationship: A user has one cart
     * This allows us to access user's cart using $user->cart
     */
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Relationship: A user has many orders
     * This allows us to access user's orders using $user->orders
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}