<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Checkout - Converting cart to order
     * This validates stock, creates order, reduces stock, and clears cart
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkout(Request $request)
    {
        // Get user's cart
        $cart = Cart::where('user_id', $request->user()->id)->first();

        // Check if cart exists
        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart not found',
            ], 404);
        }

        // Load cart items with products
        $cartItems = $cart->items()->with('product')->get();

        // Check if cart is empty
        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty',
            ], 400);
        }

        // Using database transaction to ensure data consistency
        // If any step fails, all changes are rolled back
        DB::beginTransaction();

        try {
            // Step 1: Validate stock for all items
            foreach ($cartItems as $item) {
                if (!$item->product->hasStock($item->quantity)) {
                    // Rollback transaction if insufficient stock
                    DB::rollBack();
                    
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for product: {$item->product->name}",
                        'product' => $item->product->name,
                        'available_stock' => $item->product->stock,
                        'requested_quantity' => $item->quantity,
                    ], 400);
                }
            }

            // Step 2: Calculate total amount
            $totalAmount = $cart->calculateTotal();

            // Step 3: Create the order
            $order = Order::create([
                'user_id' => $request->user()->id,
                'total_amount' => $totalAmount,
                'status' => 'pending', // default status
            ]);

            // Step 4: Create order items and reduce stock
            foreach ($cartItems as $item) {
                // Create order item with current product price
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price, 
                ]);

                // Reduce product stock
                $item->product->reduceStock($item->quantity);
            }

            // Step 5: Clear the cart
            $cart->clearCart();

            // Commit transaction that is all changes are saved
            DB::commit();

            // Load order relationships for response
            $order->load('items.product');

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'data' => [
                    'order' => $order,
                ],
            ], 201); // 201 = Created

        } catch (\Exception $e) {
            // Rollback transaction on any error
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to process checkout',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's order history
     * Shows all orders for the authenticated user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userOrders(Request $request)
    {
        // Get all orders for the authenticated user
        // Load order items and products for complete information
        // orderBy() sorts orders by newest first
        $orders = Order::where('user_id', $request->user()->id)
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => $orders,
            ],
        ], 200);
    }

    /**
     * Get a specific order by ID
     * User can only view their own orders
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        // Find order with items and products
        $order = Order::with('items.product')->find($id);

        // Check if order exists
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        // Verify order belongs to authenticated user
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order' => $order,
            ],
        ], 200);
    }

    /**
     * Get all orders (ADMIN ONLY)
     * Admin can view all orders from all users
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function allOrders()
    {
        // Get all orders with user, items, and product information
        $orders = Order::with(['user', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => $orders,
            ],
        ], 200);
    }

    /**
     * Update order status (ADMIN ONLY)
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        // Validate the new status
        $request->validate([
            'status' => 'required|in:pending,paid,processing,shipped,delivered,cancelled',
        ]);

        // Find the order
        $order = Order::find($id);

        // Check if order exists
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        // Update order status
        $order->updateStatus($request->status);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'data' => [
                'order' => $order,
            ],
        ], 200);
    }
}