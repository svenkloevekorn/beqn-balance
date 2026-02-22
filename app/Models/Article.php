<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Article extends Model
{
    protected $fillable = [
        'name',
        'description',
        'unit',
        'net_price',
        'vat_rate',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    protected function casts(): array
    {
        return [
            'net_price' => 'decimal:2',
            'vat_rate' => 'decimal:2',
        ];
    }
}
