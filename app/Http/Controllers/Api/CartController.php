<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display authenticated user's cart with all items.
     */
    public function index(Request $request)
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        // Get cart items with products
        $cart->load(['items.product']);

        return response()->json([
            'success' => true,
            'data' => $cart->getCartDetails(),
        ], 200);
    }

    /**
     * Add item to cart or update quantity if it already exists.
     */
    public function addItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $product = Product::find($request->product_id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        // Check stock
        if (!$product->hasStock($request->quantity)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available.',
                'available_stock' => $product->stock,
            ], 400);
        }

        // Check if item already exists in cart
        $cartItem = CartItem::where('cart_id', $cart->id)
                            ->where('product_id', $product->id)
                            ->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $request->quantity;

            if (!$product->hasStock($newQuantity)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock for updated quantity.',
                    'available_stock' => $product->stock,
                    'current_cart_quantity' => $cartItem->quantity,
                ], 400);
            }

            $cartItem->update(['quantity' => $newQuantity]);
            $message = 'Cart item quantity updated.';
        } else {
            $cartItem = CartItem::create([
                'cart_id'    => $cart->id,
                'product_id' => $product->id,
                'quantity'   => $request->quantity,
            ]);

            $message = 'Item added to cart successfully.';
        }

        $cartItem->load('product');

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => ['cart_item' => $cartItem],
        ], 200);
    }

    /**
     * Update quantity of a specific cart item.
     */
    public function updateItem(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = CartItem::find($itemId);

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found.',
            ], 404);
        }

        if ($cartItem->cart->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $product = $cartItem->product;

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product no longer exists.',
            ], 410); 
        }

        if (!$product->hasStock($request->quantity)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock.',
                'available_stock' => $product->stock,
            ], 400);
        }

        $cartItem->update(['quantity' => $request->quantity]);
        $cartItem->load('product');

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully.',
            'data'    => ['cart_item' => $cartItem],
        ], 200);
    }

    /**
     * Remove item from cart.
     */
    public function removeItem(Request $request, $itemId)
    {
        $cartItem = CartItem::find($itemId);

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found.',
            ], 404);
        }

        if ($cartItem->cart->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully.',
        ], 200);
    }

    /**
     * Remove all items from the user's cart.
     */
    public function clearCart(Request $request)
    {
        $cart = Cart::where('user_id', $request->user()->id)->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart not found.',
            ], 404);
        }

        $cart->clearCart();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully.',
        ], 200);
    }
}
