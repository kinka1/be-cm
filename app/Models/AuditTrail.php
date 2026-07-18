<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    protected $fillable = ['user_id', 'employee_id', 'action', 'auditable_type', 'auditable_id', 'before', 'after', 'ip_address', 'user_agent'];

    protected function casts(): array
    {
        return ['before' => 'array', 'after' => 'array'];
    }
}
