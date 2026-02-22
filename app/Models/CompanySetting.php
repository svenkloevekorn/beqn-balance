<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
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
    ];

    /**
     * Gibt immer die eine Einstellungs-Instanz zurueck.
     * Erstellt automatisch einen leeren Datensatz, falls noch keiner existiert.
     */
    public static function instance(): static
    {
        return static::firstOrCreate([]);
    }
}
