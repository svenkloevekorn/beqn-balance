<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactPerson extends Model
{
    protected $table = 'contact_persons';

    protected $fillable = [
        'customer_id',
        'name',
        'position',
        'email',
        'phone',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
