<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     * This middleware checks if the authenticated user is an admin
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and is an admin
        if (!auth()->check() || !auth()->user()->is_admin) {
            // Return 403 Forbidden response if not admin
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You are not an Admin.'
            ], 403);
        }

        // If user is admin, allow the request to proceed
        return $next($request);
    }
}