<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Services\Pos\BniQrisPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BniQrisDevelopmentController extends Controller
{
    public function createTest(Request $request, BniQrisPaymentService $bniQris): JsonResponse
    {
        $data = $request->validate([
            'order_id' => ['nullable', 'string', 'max:64'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        $data['order_id'] ??= 'DEV-BNI-'.now()->format('YmdHis').'-'.random_int(1000, 9999);

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $bniQris->createRawQris($data),
        ]);
    }
}
