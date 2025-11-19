<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, OrderService $orderService)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'items' => 'required|array|min:1|max:50',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1|max:100',
        ]);

        try {
            $order = $orderService->createOrder($validated);

            return response()->json([
                'success' => true,
                'data' => $order,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $th->getMessage(),
            ], 500);
        }
    }
}
