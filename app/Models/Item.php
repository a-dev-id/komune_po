<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'category',
        'brand',
        'default_unit',
        'default_specification',
        'image',
        'last_price',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'last_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
