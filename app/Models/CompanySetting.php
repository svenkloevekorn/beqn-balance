<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CompanySetting extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'company_name',
        'legal_form',
        'managing_director',
        'street',
        'zip',
        'city',
        'country',
        'phone',
        'fax',
        'email',
        'website',
        'vat_id',
        'tax_number',
        'trade_register',
        'bank_name',
        'iban',
        'bic',
        'logo_path',
        'letterhead_path',
        'use_letterhead',
        'dunning_reminder_fee',
        'dunning_reminder_subject',
        'dunning_reminder_text',
        'dunning_reminder_days',
        'dunning_first_fee',
        'dunning_first_subject',
        'dunning_first_text',
        'dunning_first_days',
        'dunning_second_fee',
        'dunning_second_subject',
        'dunning_second_text',
        'dunning_second_days',
    ];

    protected function casts(): array
    {
        return [
            'use_letterhead' => 'boolean',
        ];
    }

    /**
     * Gibt immer die eine Einstellungs-Instanz zurueck.
     * Erstellt automatisch einen leeren Datensatz, falls noch keiner existiert.
     */
    public static function instance(): static
    {
        return static::firstOrCreate([]);
    }
}
