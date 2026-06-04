<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = [
        'product_id',
        'ingredient_id',
        'quantity_needed',
        'unit',
    ];
}
