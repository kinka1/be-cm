<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Employee extends Model
{
    protected $fillable = [
        'full_name',
        'store_id',
        'email',
        'phone',
        'address',
        'date_of_birth',
        'join_date',
        'role_id',
        'photo_url',
        'ktp_url',
        'kk_url',
        'status',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'employee_store')->withTimestamps();
    }
}
