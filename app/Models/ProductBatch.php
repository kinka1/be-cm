<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    protected $fillable = ['store_id', 'product_id', 'batch_number', 'expired_date', 'quantity', 'received_date', 'notes'];
}
