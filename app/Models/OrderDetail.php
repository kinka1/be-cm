<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
        'notes',
        'created_at',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
