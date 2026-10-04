<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosCartItem extends Model
{
    protected $fillable = [
        'pos_cart_id',
        'item_type',
        'product_id',
        'custom_name',
        'custom_unit_price',
        'quantity',
        'notes',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(PosCart::class, 'pos_cart_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function modifiers(): HasMany
    {
        return $this->hasMany(PosCartItemModifier::class, 'cart_item_id');
    }
}
