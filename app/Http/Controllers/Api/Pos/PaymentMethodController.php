<?php

namespace App\Http\Controllers\Api\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PaymentMethodController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => [
                [
                    'value' => 'cash',
                    'label' => 'Tunai',
                    'requires_amount_paid' => true,
                    'has_change' => true,
                ],
                [
                    'value' => 'qris',
                    'label' => 'QRIS',
                    'requires_amount_paid' => false,
                    'has_change' => false,
                ],
                [
                    'value' => 'transfer',
                    'label' => 'Transfer',
                    'requires_amount_paid' => false,
                    'has_change' => false,
                ],
            ],
        ]);
    }
}
