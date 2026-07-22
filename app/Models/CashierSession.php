<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashierSession extends Model
{
    protected $fillable = [
        'store_id',
        'employee_id',
        'opened_by',
        'closed_by',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'cash_difference',
        'status',
        'opened_at',
        'closed_at',
        'opening_notes',
        'closing_notes',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cashMovements(): HasMany
    {
        return $this->hasMany(CashierCashMovement::class);
    }
}
