<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     
     * This always returns success and updates the order status to 'paid'
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function simulate(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        // Find the order
        $order = Order::find($request->order_id);

        // Verify order belongs to authenticated user 
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. This order does not belong to you.',
            ], 403);
        }

        // Check if order is already paid
        if ($order->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Order has already been paid',
                'order_status' => $order->status,
            ], 400);
        }

       
        $order->updateStatus('paid');

        // Return success response
        return response()->json([
            'success' => true,
            'payment_successful' => true, 
            'message' => 'Payment processed successfully',
            'data' => [
                'order' => $order->fresh(), // fresh() reloads order from database
                'payment_details' => [
                    'amount_paid' => $order->total_amount,
                    'payment_method' => 'simulated',
                    'payment_date' => now()->toDateTimeString(),
                ],
            ],
        ], 200);
    }
}