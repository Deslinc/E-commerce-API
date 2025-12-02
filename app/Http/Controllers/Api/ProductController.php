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
     * Accepts either:
     *  - file upload under "image" (form-data), OR
     *  - remote image URL in "image_url" (JSON or form-data)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate incoming request.
        // 'image' is an optional uploaded file; 'image_url' is an optional remote URL.
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_url' => 'nullable|url',
        ]);

        // Resolve final image_url:
        // prefer uploaded file over provided URL.
        $imageUrl = null;

        if ($request->hasFile('image')) {
            // Store uploaded image to public disk (storage/app/public/products)
            $path = $request->file('image')->store('products', 'public');
            $imageUrl = Storage::url($path); // e.g. /storage/products/abcd.jpg
        } elseif ($request->filled('image_url')) {
            // Use the provided external URL
            $imageUrl = $request->input('image_url');
        }

        // Create new product using validated data and resolved image_url
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
     * Accepts either a new uploaded file (image) or a new image_url string.
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
            'image_url' => 'nullable|url',
        ]);

        // If a new image file is uploaded, store it and delete previous local file (if any)
        if ($request->hasFile('image')) {
            if ($product->image_url) {
                // If previous image_url pointed to local storage (starts with /storage/), delete the old file
                $oldPath = parse_url($product->image_url, PHP_URL_PATH); // e.g. /storage/products/old.jpg or /products/old.jpg
                if (strpos($oldPath, '/storage/') === 0) {
                    // Convert to disk path (remove leading '/storage/')
                    $oldPath = ltrim(str_replace('/storage/', '', $oldPath), '/');
                    Storage::disk('public')->delete($oldPath);
                }
                // If previous image_url was an external URL, we do not attempt to delete remote file.
            }

            // Store new uploaded image
            $path = $request->file('image')->store('products', 'public');
            $product->image_url = Storage::url($path);
        } elseif ($request->filled('image_url')) {
            // If client provided an external URL, set it as the image_url.
            // (Optional: remove a previously stored local file here if desired)
            $product->image_url = $request->input('image_url');
        }

        // Update other fields (exclude the 'image' file input)
        $product->fill($request->except(['image']));
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

        // Delete product image if it exists and was stored locally
        if ($product->image_url) {
            $oldPath = parse_url($product->image_url, PHP_URL_PATH);
            if (strpos($oldPath, '/storage/') === 0) {
                $oldPath = ltrim(str_replace('/storage/', '', $oldPath), '/');
                Storage::disk('public')->delete($oldPath);
            }
            // If image_url is remote, we do not delete remote files.
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

        // Increase stock using model method (if it exists) or fallback
        if (method_exists($product, 'increaseStock')) {
            $product->increaseStock($request->quantity);
        } else {
            $product->stock = $product->stock + (int)$request->quantity;
            $product->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Product restocked successfully',
            'data' => [
                'product' => $product->fresh(),
            ],
        ], 200);
    }
}
