<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;

/*
|
| API Routes
| Here is where I can register API routes for my application.
| Routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// All users routes or Public routes (No authentication required)

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Public product routes - anyone can view products
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']); // List all products
    Route::get('/{id}', [ProductController::class, 'show']); // View single product
});

// PROTECTED ROUTES (Authentication required)
// middleware('auth:sanctum') ensures user is logged in via token

Route::middleware('auth:sanctum')->group(function () {
    
    // Auth routes for authenticated users
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
    });

    // Cart management routes
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']); // View cart
        Route::post('/items', [CartController::class, 'addItem']); // Add item to cart
        Route::put('/items/{id}', [CartController::class, 'updateItem']); // Update item quantity
        Route::delete('/items/{id}', [CartController::class, 'removeItem']); // Remove item
        Route::delete('/', [CartController::class, 'clearCart']); // Clear entire cart
    });

    // Order routes for regular users
    Route::prefix('orders')->group(function () {
        Route::post('/checkout', [OrderController::class, 'checkout']); // Create order from cart
        Route::get('/', [OrderController::class, 'userOrders']); // View user's orders
        Route::get('/{id}', [OrderController::class, 'show']); // View specific order
    });

    // Payment simulation route
    Route::prefix('payment')->group(function () {
        Route::post('/simulate', [PaymentController::class, 'simulate']);
    });

    // ADMIN-ONLY ROUTES
    // middleware admin ensures only admin users can access these routes
    
    Route::middleware('admin')->group(function () {
        
        // Product management (CRUD operations)
        Route::prefix('admin/products')->group(function () {
            Route::post('/', [ProductController::class, 'store']); // Create product
            Route::put('/{id}', [ProductController::class, 'update']); // Update product
            Route::delete('/{id}', [ProductController::class, 'destroy']); // Delete product
            Route::post('/{id}/restock', [ProductController::class, 'restock']); // Restock product
        });

        // Order management for admins
        Route::prefix('admin/orders')->group(function () {
            Route::get('/', [OrderController::class, 'allOrders']); // View all orders
            Route::put('/{id}/status', [OrderController::class, 'updateStatus']); // Update order status
        });
    });
});