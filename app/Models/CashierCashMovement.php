<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashierCashMovement extends Model
{
    protected $fillable = [
        'cashier_session_id',
        'store_id',
        'employee_id',
        'type',
        'amount',
        'category',
        'description',
    ];

    public function cashierSession(): BelongsTo
    {
        return $this->belongsTo(CashierSession::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
