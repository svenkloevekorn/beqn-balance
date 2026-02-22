<?php

namespace App\Models;

use App\Services\NumberFormatService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NumberRange extends Model
{
    protected $fillable = [
        'type',
        'label',
        'format',
        'counter_global',
        'counter_yearly',
        'counter_monthly',
        'counter_daily',
        'last_reset_year',
        'last_reset_month',
        'last_reset_day',
    ];

    protected function casts(): array
    {
        return [
            'counter_global' => 'integer',
            'counter_yearly' => 'integer',
            'counter_monthly' => 'integer',
            'counter_daily' => 'integer',
            'last_reset_year' => 'integer',
            'last_reset_month' => 'integer',
            'last_reset_day' => 'integer',
        ];
    }

    /**
     * Generiert die naechste Nummer fuer den angegebenen Typ.
     * Race-Condition-sicher durch DB-Transaktion + Row-Lock.
     */
    public static function generateNext(string $type, ?Carbon $date = null): string
    {
        $date ??= Carbon::now();
        $service = app(NumberFormatService::class);

        return DB::transaction(function () use ($type, $date, $service) {
            $range = static::where('type', $type)->lockForUpdate()->firstOrFail();

            $range->resetCountersIfNeeded($date, $service);
            $range->incrementRequiredCounters($service);
            $range->save();

            return $service->generate($range->format, $range->getCounterValues(), $date);
        });
    }

    /**
     * Vorschau der naechsten Nummer (ohne Zaehler zu erhoehen).
     */
    public function previewNext(?Carbon $date = null): string
    {
        $date ??= Carbon::now();
        $service = app(NumberFormatService::class);

        // Simuliere Reset + Inkrement auf Kopien
        $counters = $this->getCounterValues();
        $required = $service->getRequiredCounters($this->format);

        foreach ($required as $counterName) {
            $resetType = $service->getCounterResetType($counterName);
            $column = $service->getCounterColumn($counterName);

            if ($this->shouldReset($resetType, $date)) {
                $counters[$column] = 0;
            }

            $counters[$column]++;
        }

        return $service->generate($this->format, $counters, $date);
    }

    /**
     * Setzt Zaehler zurueck, falls ein neuer Zeitraum begonnen hat.
     */
    protected function resetCountersIfNeeded(Carbon $date, NumberFormatService $service): void
    {
        $required = $service->getRequiredCounters($this->format);

        foreach ($required as $counterName) {
            $resetType = $service->getCounterResetType($counterName);
            $column = $service->getCounterColumn($counterName);

            if ($this->shouldReset($resetType, $date)) {
                $this->{$column} = 0;
            }
        }

        // Reset-Zeitstempel aktualisieren
        $this->last_reset_year = $date->year;
        $this->last_reset_month = $date->month;
        $this->last_reset_day = $date->day;
    }

    /**
     * Prueft ob ein Zaehler zurueckgesetzt werden muss.
     */
    protected function shouldReset(string $resetType, Carbon $date): bool
    {
        return match ($resetType) {
            'year' => $this->last_reset_year !== $date->year,
            'month' => $this->last_reset_year !== $date->year
                || $this->last_reset_month !== $date->month,
            'day' => $this->last_reset_year !== $date->year
                || $this->last_reset_month !== $date->month
                || $this->last_reset_day !== $date->day,
            default => false, // 'none' = kein Reset
        };
    }

    /**
     * Inkrementiert alle im Format benoetigten Zaehler.
     */
    protected function incrementRequiredCounters(NumberFormatService $service): void
    {
        $required = $service->getRequiredCounters($this->format);

        foreach ($required as $counterName) {
            $column = $service->getCounterColumn($counterName);
            $this->{$column}++;
        }
    }

    /**
     * Gibt die aktuellen Zaehlerstaende als Array zurueck.
     */
    public function getCounterValues(): array
    {
        return [
            'counter_global' => $this->counter_global,
            'counter_yearly' => $this->counter_yearly,
            'counter_monthly' => $this->counter_monthly,
            'counter_daily' => $this->counter_daily,
        ];
    }
}
