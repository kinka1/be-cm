<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = ['store_id', 'po_number', 'supplier_id', 'employee_id', 'order_date', 'received_date', 'status', 'total_amount', 'notes'];

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
