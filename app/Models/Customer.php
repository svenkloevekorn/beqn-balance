<?php

namespace App\Models;

use App\Models\Traits\HasContactFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasContactFields;

    protected $fillable = [
        'name',
        'street',
        'zip',
        'city',
        'country',
        'email',
        'phone',
        'vat_id',
        'payment_term_days',
        'discount_percent',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'discount_percent' => 'decimal:2',
        ];
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function contactPersons(): HasMany
    {
        return $this->hasMany(ContactPerson::class);
    }
}
