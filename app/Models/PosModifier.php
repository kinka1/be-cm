<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PosModifier extends Model
{
    protected $fillable = [
        'store_id',
        'name',
        'price_delta',
        'is_active',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'pos_category_modifiers', 'modifier_id', 'category_id')
            ->withPivot(['store_id', 'is_active'])
            ->withTimestamps();
    }
}
