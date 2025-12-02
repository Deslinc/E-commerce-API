<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of all products (All users or public)
     * Anyone can view the product list
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Get all products from database
        $products = Product::all();

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products,
            ],
        ], 200);
    }

    /**
     * Display a single product by ID (all users or public)
     * Anyone can view product details
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Find product by ID
        $product = Product::find($id);

        // Check if product exists
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
            ],
        ], 200);
    }

    /**
     * Store a new product (ADMIN ONLY)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload if present
        $imageUrl = null;
        if ($request->hasFile('image')) {
            // Store image in 'public/products' directory
            // This will create storage/app/public/products folder
            $path = $request->file('image')->store('products', 'public');
            
            // Generate full URL for the image
            $imageUrl = Storage::url($path);
        }

        // Create new product with validated data
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image_url' => $imageUrl,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => [
                'product' => $product,
            ],
        ], 201);
    }

    /**
     * Update an existing product (ADMIN ONLY)
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Find the product
        $product = Product::find($id);

        // Check if product exists
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        // Validate incoming request
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload if new image is provided
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($product->image_url) {
                // Extract path from URL and delete
                $oldPath = str_replace('/storage/', '', parse_url($product->image_url, PHP_URL_PATH));
                Storage::disk('public')->delete($oldPath);
            }

            // Store new image
            $path = $request->file('image')->store('products', 'public');
            $product->image_url = Storage::url($path);
        }

        // Update product with new data
        // fill() method updates only the fields present in the request
        $product->fill($request->except('image'));
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => [
                'product' => $product,
            ],
        ], 200);
    }

    /**
     * Delete a product (ADMIN ONLY)
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Find the product
        $product = Product::find($id);

        // Check if product exists
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        // Delete product image if it exists
        if ($product->image_url) {
            $oldPath = str_replace('/storage/', '', parse_url($product->image_url, PHP_URL_PATH));
            Storage::disk('public')->delete($oldPath);
        }

        // Delete the product from database
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ], 200);
    }

    /**
     * Restock a product (ADMIN ONLY)
     * Adds stock to an existing product
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restock(Request $request, $id)
    {
        // Find the product
        $product = Product::find($id);

        // Check if product exists
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        // Validate the quantity to add
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Increase stock using model method
        $product->increaseStock($request->quantity);

        return response()->json([
            'success' => true,
            'message' => 'Product restocked successfully',
            'data' => [
                'product' => $product->fresh(), // fresh() reloads the model from database
            ],
        ], 200);
    }
}