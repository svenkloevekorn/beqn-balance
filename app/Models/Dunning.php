<?php

namespace App\Models;

use App\Enums\DunningLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dunning extends Model
{
    protected $fillable = [
        'invoice_id',
        'level',
        'dunning_date',
        'due_date',
        'fee',
        'subject',
        'text',
    ];

    protected function casts(): array
    {
        return [
            'level' => DunningLevel::class,
            'dunning_date' => 'date',
            'due_date' => 'date',
            'fee' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
