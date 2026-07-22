<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    protected $fillable = [
        'store_id',
        'category_name',
        'description',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
