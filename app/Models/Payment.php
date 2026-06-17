<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'order_id',
        'payment_method',
        'payment_gateway',
        'amount_paid',
        'change_amount',
        'qris_transaction_id',
        'gateway_order_id',
        'gateway_transaction_id',
        'gateway_response',
        'payment_fee',
        'payment_date',
        'payment_status',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'gateway_response' => 'array',
        ];
    }
}
