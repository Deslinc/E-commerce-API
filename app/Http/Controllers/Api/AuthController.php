<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     * Creates a new user account and automatically creates a cart for the user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Validate incoming request data
        // name: required string with max 255 characters
        // email: required, must be valid email format, must be unique
        // password: required, minimum 8 characters, must be confirmed
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create new user with validated data
        // Password is automatically hashed 
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'is_admin' => false,
        ]);

        // Create a cart for the new user
        // Every user should have their own cart
        Cart::create([
            'user_id' => $user->id,
        ]);

        // Creating an authentication token for the user
        // Token name 'auth_token' helps identify the token's purpose
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return success response with user data and token
        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 201); 
    }

    /**
     * Login an existing user
     * Validates credentials and gives an authentication token
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validate login credentials
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        // Check if user exists and if password is correct
        //  comparing the plain password with hashed password in database
        if (!$user || !Hash::check($request->password, $user->password)) {
            // Return validation error if credentials are invalid
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Create authentication token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return success response with user data and token
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 200);
    }

    /**
     * Logout the authenticated user
     * Deletes all authentication tokens for the user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Delete all tokens for the authenticated user
        // This logs the user out from all devices
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ], 200);
    }

    /**
     * Get authenticated user's profile
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user(),
            ],
        ], 200);
    }
}