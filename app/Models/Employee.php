<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'full_name',
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
}
