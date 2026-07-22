<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOpname extends Model
{
    protected $fillable = ['store_id', 'opname_number', 'employee_id', 'opname_date', 'status', 'notes', 'submitted_at', 'approved_at', 'approved_by'];

    public function items(): HasMany
    {
        return $this->hasMany(StockOpnameItem::class);
    }
}
