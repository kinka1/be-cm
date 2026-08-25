<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetailModifier extends Model
{
    protected $fillable = [
        'order_detail_id',
        'modifier_id',
        'name',
        'price_delta',
        'quantity',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function orderDetail(): BelongsTo
    {
        return $this->belongsTo(OrderDetail::class);
    }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(PosModifier::class, 'modifier_id');
    }
}
