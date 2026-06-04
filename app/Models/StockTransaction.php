<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
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
}
