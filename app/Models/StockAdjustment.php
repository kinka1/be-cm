<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $fillable = ['store_id', 'product_id', 'quantity', 'adjustment_type', 'requested_by', 'approved_by', 'status', 'reason', 'approval_notes', 'approved_at', 'stock_transaction_id'];
}
