<?php

namespace App\Models;

use App\Models\Traits\HasContactFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Supplier extends Model
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
    ];

    public function incomingInvoices(): HasMany
    {
        return $this->hasMany(IncomingInvoice::class);
    }
}
