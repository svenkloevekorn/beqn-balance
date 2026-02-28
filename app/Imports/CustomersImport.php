<?php

namespace App\Imports;

use App\Models\Customer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CustomersImport implements ToCollection, WithHeadingRow, WithValidation
{
    public int $imported = 0;
    public int $updated = 0;
    public int $skipped = 0;
    public array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 weil Zeile 1 = Header

            $name = trim($row['name'] ?? '');
            if ($name === '') {
                $this->skipped++;
                continue;
            }

            $data = [
                'name' => $name,
                'street' => trim($row['strasse'] ?? $row['street'] ?? ''),
                'zip' => trim($row['plz'] ?? $row['zip'] ?? ''),
                'city' => trim($row['ort'] ?? $row['city'] ?? ''),
                'country' => trim($row['land'] ?? $row['country'] ?? '') ?: 'DE',
                'email' => trim($row['email'] ?? ''),
                'phone' => trim($row['telefon'] ?? $row['phone'] ?? ''),
                'vat_id' => trim($row['ust_id'] ?? $row['vat_id'] ?? ''),
                'payment_term_days' => (int) ($row['zahlungsziel_tage'] ?? $row['payment_term_days'] ?? 14) ?: 14,
                'discount_percent' => $row['rabatt_prozent'] ?? $row['discount_percent'] ?? null,
                'buyer_reference' => trim($row['kaeufer_referenz'] ?? $row['buyer_reference'] ?? ''),
                'notes' => trim($row['notizen'] ?? $row['notes'] ?? ''),
            ];

            // Leere Strings zu null konvertieren
            $data = array_map(fn ($value) => $value === '' ? null : $value, $data);
            $data['name'] = $name; // Name darf nicht null sein

            $existing = Customer::where('name', $name)->first();

            if ($existing) {
                $existing->update($data);
                $this->updated++;
            } else {
                Customer::create($data);
                $this->imported++;
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            '*.name' => 'required|string|max:255',
        ];
    }

    public function headingRow(): int
    {
        return 1;
    }
}
