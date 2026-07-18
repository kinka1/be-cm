<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    protected $fillable = ['product_id', 'batch_number', 'expired_date', 'quantity', 'received_date', 'notes'];
}
