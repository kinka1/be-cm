<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalonMantu extends Model
{
    protected $table = 'calon_mantu';

    const UPDATED_AT = null;

    protected $fillable = [
        'table_number',
        'qr_code',
        'capacity',
        'status',
        'created_at',
    ];
}
