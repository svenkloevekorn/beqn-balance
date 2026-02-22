<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NumberRange extends Model
{
    protected $fillable = [
        'type',
        'label',
        'prefix',
        'include_year',
        'next_number',
        'digits',
        'reset_yearly',
        'last_reset_year',
    ];

    protected function casts(): array
    {
        return [
            'include_year' => 'boolean',
            'reset_yearly' => 'boolean',
            'next_number' => 'integer',
            'digits' => 'integer',
        ];
    }

    /**
     * Generiert die naechste Nummer fuer den angegebenen Typ.
     *
     * Beispiel: RE-2026-0001, AN-2026-0002, KD-1001
     */
    public static function generateNext(string $type): string
    {
        $range = static::where('type', $type)->lockForUpdate()->firstOrFail();

        $year = now()->year;

        // Jaehrlichen Reset pruefen
        if ($range->reset_yearly && $range->last_reset_year !== $year) {
            $range->next_number = 1;
            $range->last_reset_year = $year;
        }

        $number = $range->next_number;
        $range->increment('next_number');
        $range->update(['last_reset_year' => $year]);

        // Nummer formatieren
        $formatted = $range->digits > 0
            ? str_pad($number, $range->digits, '0', STR_PAD_LEFT)
            : (string) $number;

        if ($range->include_year) {
            return "{$range->prefix}-{$year}-{$formatted}";
        }

        return "{$range->prefix}-{$formatted}";
    }

    /**
     * Zeigt eine Vorschau der naechsten Nummer (ohne zu inkrementieren).
     */
    public function previewNext(): string
    {
        $year = now()->year;
        $number = $this->next_number;

        // Simuliere Reset
        if ($this->reset_yearly && $this->last_reset_year !== $year) {
            $number = 1;
        }

        $formatted = $this->digits > 0
            ? str_pad($number, $this->digits, '0', STR_PAD_LEFT)
            : (string) $number;

        if ($this->include_year) {
            return "{$this->prefix}-{$year}-{$formatted}";
        }

        return "{$this->prefix}-{$formatted}";
    }
}
