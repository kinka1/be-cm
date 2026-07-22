<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'store_id',
        'product_id',
        'transaction_type',
        'quantity',
        'reference_type',
        'reference_id',
        'employee_id',
        'notes',
        'transaction_date',
        'created_at',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
