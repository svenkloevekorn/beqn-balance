<?php

namespace App\Services;

use Carbon\Carbon;

class NumberFormatService
{
    /**
     * Alle erlaubten Platzhalter-Definitionen.
     * Jeder Eintrag: Pattern (Regex) => Resolver-Callable.
     *
     * Neue Platzhalter koennen hier ergaenzt werden.
     */
    protected array $datePlaceholders = [
        'datum' => 'resolveDatum',
        'jjjj' => 'resolveJjjj',
        'jj' => 'resolveJj',
        'mm' => 'resolveMm',
        'm' => 'resolveM',
        'tt' => 'resolveTt',
        't' => 'resolveT',
    ];

    /**
     * Zaehler-Platzhalter: Name => Reset-Typ.
     * 'none' = kein Reset, 'year' = jaehrlich, 'month' = monatlich, 'day' = taeglich.
     */
    protected array $counterPlaceholders = [
        'z' => 'none',
        'jz' => 'year',
        'mz' => 'month',
        'tz' => 'day',
    ];

    // --- Validierung ---

    /**
     * Validiert einen Format-String.
     *
     * @return array<string> Liste von Fehlermeldungen (leer = gueltig)
     */
    public function validate(string $format): array
    {
        $errors = [];

        if (trim($format) === '') {
            $errors[] = 'Format darf nicht leer sein.';

            return $errors;
        }

        // Alle Platzhalter extrahieren
        preg_match_all('/\{([^}]*)\}/', $format, $matches);
        $placeholders = $matches[1] ?? [];

        // Pruefen ob mindestens ein Zaehler-Platzhalter vorhanden
        $hasCounter = false;

        foreach ($placeholders as $placeholder) {
            $parsed = $this->parsePlaceholder($placeholder);

            if ($parsed === null) {
                $errors[] = "Ungueltiger Platzhalter: {{$placeholder}}";

                continue;
            }

            if ($parsed['type'] === 'counter') {
                $hasCounter = true;
            }
        }

        if (! $hasCounter && empty($errors)) {
            $errors[] = 'Format muss mindestens einen Zaehler-Platzhalter enthalten ({z}, {jz}, {mz} oder {tz}).';
        }

        // Zeichen ausserhalb von Platzhaltern pruefen
        $withoutPlaceholders = preg_replace('/\{[^}]*\}/', '', $format);
        if (preg_match('/[^A-Za-z0-9\-]/', $withoutPlaceholders)) {
            $errors[] = 'Format darf ausserhalb von Platzhaltern nur A-Z, a-z, 0-9 und Bindestrich enthalten.';
        }

        return $errors;
    }

    /**
     * Parst einen einzelnen Platzhalter-Inhalt (ohne geschweifte Klammern).
     *
     * @return array{type: string, name: string, minDigits: int}|null
     */
    protected function parsePlaceholder(string $content): ?array
    {
        // Zaehler mit optionaler Mindestlaenge: z, z,3, jz,5 etc.
        if (preg_match('/^(z|jz|mz|tz)(?:,(\d+))?$/', $content, $m)) {
            return [
                'type' => 'counter',
                'name' => $m[1],
                'minDigits' => isset($m[2]) ? (int) $m[2] : 0,
            ];
        }

        // Datum-Platzhalter
        if (array_key_exists($content, $this->datePlaceholders)) {
            return [
                'type' => 'date',
                'name' => $content,
                'minDigits' => 0,
            ];
        }

        return null;
    }

    // --- Generierung ---

    /**
     * Generiert eine Nummer aus dem Format-String.
     *
     * @param array{counter_global: int, counter_yearly: int, counter_monthly: int, counter_daily: int} $counters
     *        Die BEREITS inkrementierten Zaehlerstaende.
     */
    public function generate(string $format, array $counters, ?Carbon $date = null): string
    {
        $date ??= Carbon::now();

        // Platzhalter ersetzen (laengste zuerst, um Konflikte zu vermeiden)
        $result = preg_replace_callback('/\{([^}]*)\}/', function ($match) use ($counters, $date) {
            $content = $match[1];
            $parsed = $this->parsePlaceholder($content);

            if ($parsed === null) {
                return $match[0]; // Unbekannten Platzhalter unveraendert lassen
            }

            if ($parsed['type'] === 'date') {
                return $this->resolveDate($parsed['name'], $date);
            }

            if ($parsed['type'] === 'counter') {
                return $this->resolveCounter($parsed['name'], $parsed['minDigits'], $counters);
            }

            return $match[0];
        }, $format);

        return $result;
    }

    /**
     * Gibt zurueck, welche Zaehler-Typen im Format benoetigt werden.
     *
     * @return array<string> z.B. ['z'], ['jz', 'mz'], etc.
     */
    public function getRequiredCounters(string $format): array
    {
        $required = [];

        preg_match_all('/\{([^}]*)\}/', $format, $matches);

        foreach ($matches[1] as $content) {
            $parsed = $this->parsePlaceholder($content);
            if ($parsed && $parsed['type'] === 'counter') {
                $required[] = $parsed['name'];
            }
        }

        return array_unique($required);
    }

    // --- Datum-Resolver ---

    protected function resolveDate(string $name, Carbon $date): string
    {
        return match ($name) {
            'jjjj' => $date->format('Y'),
            'jj' => $date->format('y'),
            'm' => (string) $date->month,
            'mm' => $date->format('m'),
            't' => (string) $date->day,
            'tt' => $date->format('d'),
            'datum' => $date->format('Ymd'),
            default => "{{$name}}",
        };
    }

    // --- Zaehler-Resolver ---

    protected function resolveCounter(string $name, int $minDigits, array $counters): string
    {
        $value = match ($name) {
            'z' => $counters['counter_global'] ?? 0,
            'jz' => $counters['counter_yearly'] ?? 0,
            'mz' => $counters['counter_monthly'] ?? 0,
            'tz' => $counters['counter_daily'] ?? 0,
            default => 0,
        };

        if ($minDigits > 0) {
            return str_pad((string) $value, $minDigits, '0', STR_PAD_LEFT);
        }

        return (string) $value;
    }

    /**
     * Gibt den Reset-Typ fuer einen Zaehler-Namen zurueck.
     */
    public function getCounterResetType(string $counterName): string
    {
        return $this->counterPlaceholders[$counterName] ?? 'none';
    }

    /**
     * Mapping: Zaehler-Name → Datenbank-Spalte.
     */
    public function getCounterColumn(string $counterName): string
    {
        return match ($counterName) {
            'z' => 'counter_global',
            'jz' => 'counter_yearly',
            'mz' => 'counter_monthly',
            'tz' => 'counter_daily',
            default => throw new \InvalidArgumentException("Unbekannter Zaehler: {$counterName}"),
        };
    }
}
