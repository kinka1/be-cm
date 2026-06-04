<?php

namespace App\Models;

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
        'total_amount',
        'payment_method',
        'payment_status',
        'order_status',
    ];
}
