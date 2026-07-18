<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_name',
        'sku',
        'category_id',
        'description',
        'unit_of_measure',
        'minimum_stock',
        'current_stock',
        'cost_price',
        'selling_price',
        'is_active',
    ];

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    public function stockTransactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }
}
