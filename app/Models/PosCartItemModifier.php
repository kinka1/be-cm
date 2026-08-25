<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosCartItemModifier extends Model
{
    protected $fillable = [
        'cart_item_id',
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

    public function cartItem(): BelongsTo
    {
        return $this->belongsTo(PosCartItem::class, 'cart_item_id');
    }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(PosModifier::class, 'modifier_id');
    }
}
