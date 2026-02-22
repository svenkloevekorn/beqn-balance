<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'name',
        'description',
        'unit',
        'net_price',
        'vat_rate',
    ];

    protected function casts(): array
    {
        return [
            'net_price' => 'decimal:2',
            'vat_rate' => 'decimal:2',
        ];
    }
}
