<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosCategoryModifier extends Model
{
    protected $fillable = [
        'store_id',
        'category_id',
        'modifier_id',
        'is_active',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(PosModifier::class, 'modifier_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
