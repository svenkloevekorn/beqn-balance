<?php

namespace App\Imports;

use App\Models\Article;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ArticlesImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;
    public int $updated = 0;
    public int $skipped = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $name = trim($row['name'] ?? '');
            if ($name === '') {
                $this->skipped++;
                continue;
            }

            $data = [
                'name' => $name,
                'description' => trim($row['beschreibung'] ?? $row['description'] ?? ''),
                'unit' => trim($row['einheit'] ?? $row['unit'] ?? '') ?: 'Stück',
                'net_price' => $this->parseDecimal($row['nettopreis'] ?? $row['net_price'] ?? 0),
                'vat_rate' => $this->parseDecimal($row['mwst_satz'] ?? $row['vat_rate'] ?? 19),
                'is_active' => $this->parseBoolean($row['aktiv'] ?? $row['is_active'] ?? '1'),
            ];

            $data = array_map(fn ($value) => $value === '' ? null : $value, $data);
            $data['name'] = $name;

            $existing = Article::where('name', $name)->first();

            if ($existing) {
                $existing->update($data);
                $this->updated++;
            } else {
                Article::create($data);
                $this->imported++;
            }
        }
    }

    protected function parseDecimal(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Komma als Dezimaltrenner unterstützen (deutsch)
        $value = str_replace(',', '.', (string) $value);

        return is_numeric($value) ? (float) $value : 0;
    }

    protected function parseBoolean(mixed $value): bool
    {
        $value = strtolower(trim((string) $value));

        return in_array($value, ['1', 'ja', 'yes', 'true', 'aktiv', 'x']);
    }

    public function headingRow(): int
    {
        return 1;
    }
}
