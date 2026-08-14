<?php

namespace App\Http\Controllers\Api\Pos;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $this->orderQuery($request)->get(),
        ]);
    }

    public function paginated(Request $request): JsonResponse
    {
        $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $this->orderQuery($request)->paginate($request->integer('per_page', 15)),
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $order->load(['store', 'details.product', 'payment']),
        ]);
    }

    private function orderQuery(Request $request): Builder
    {
        $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $query = Order::query()->with(['store', 'details.product', 'payment'])->orderByDesc('order_date');

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->integer('store_id'));
        }

        if ($request->filled('order_status')) {
            $query->where('order_status', $request->string('order_status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->string('payment_status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('order_date', $request->string('date'));
        }

        return $query;
    }
}
