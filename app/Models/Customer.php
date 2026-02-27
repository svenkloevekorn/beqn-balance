<?php

namespace App\Models;

use App\Models\Traits\HasContactFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Customer extends Model
{
    use HasContactFields, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

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
        'buyer_reference',
        'notes',
        'has_custom_prices',
    ];

    protected function casts(): array
    {
        return [
            'discount_percent' => 'decimal:2',
            'has_custom_prices' => 'boolean',
        ];
    }

    public function customPrices(): HasMany
    {
        return $this->hasMany(CustomerArticlePrice::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function contactPersons(): HasMany
    {
        return $this->hasMany(ContactPerson::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function deliveryNotes(): HasMany
    {
        return $this->hasMany(DeliveryNote::class);
    }
}
