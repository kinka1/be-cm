<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderDetail extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'order_id',
        'item_type',
        'product_id',
        'item_name',
        'quantity',
        'unit_price',
        'subtotal',
        'notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function modifiers(): HasMany
    {
        return $this->hasMany(OrderDetailModifier::class);
    }
}
