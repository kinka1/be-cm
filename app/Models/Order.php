<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'table_id',
        'order_type',
        'customer_name',
        'employee_id',
        'order_date',
        'subtotal',
        'tax',
        'discount',
        'payment_fee',
        'total_amount',
        'payment_method',
        'payment_status',
        'order_status',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
