<?php

namespace App\Http\Controllers\Api\Pos;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::query()->with(['details.product', 'payment'])->orderByDesc('order_date');

        if ($request->filled('order_status')) {
            $query->where('order_status', $request->string('order_status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->string('payment_status'));
        }

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $query->paginate(15),
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $order->load(['details.product', 'payment']),
        ]);
    }
}
