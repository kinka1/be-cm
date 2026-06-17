<?php

namespace App\Http\Controllers\Api\Pos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class OrderStatusController extends Controller
{
    public function update(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        if ($order->order_status === 'completed') {
            return response()->json([
                'status' => 'gagal',
                'message' => 'order sudah completed',
                'data' => null,
            ], 422);
        }

        $order->update([
            'order_status' => $request->string('order_status'),
        ]);

        return response()->json([
            'status' => 'sukses',
            'message' => 'updated',
            'data' => $order,
        ]);
    }
}
