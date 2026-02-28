<?php

namespace App\Filament\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use Filament\Widgets\ChartWidget;

class CustomerRevenueWidget extends ChartWidget
{
    protected static ?int $sort = -5;

    protected int | string | array $columnSpan = 1;

    protected ?string $heading = 'Top Kunden nach Umsatz';

    protected ?string $description = 'Die 5 umsatzstärksten Kunden';

    protected ?string $maxHeight = '300px';

    protected ?string $pollingInterval = null;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return [
            'year' => 'Aktuelles Jahr (' . now()->year . ')',
            'total' => 'Gesamt',
        ];
    }

    protected function getData(): array
    {
        $year = $this->filter === 'total' ? null : now()->year;
        $statuses = [InvoiceStatus::Paid->value, InvoiceStatus::PartiallyPaid->value];

        $customers = Customer::query()
            ->select('customers.id', 'customers.name')
            ->selectSub(function ($query) use ($statuses, $year) {
                $query->from('invoice_items')
                    ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
                    ->whereColumn('invoices.customer_id', 'customers.id')
                    ->whereIn('invoices.status', $statuses)
                    ->when($year, fn ($q) => $q->whereYear('invoices.invoice_date', $year))
                    ->selectRaw('COALESCE(SUM(invoice_items.quantity * invoice_items.net_price * (1 + invoice_items.vat_rate / 100)), 0)');
            }, 'umsatz')
            ->having('umsatz', '>', 0)
            ->orderByDesc('umsatz')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Umsatz (€)',
                    'data' => $customers->pluck('umsatz')->map(fn ($v) => round($v, 2))->toArray(),
                    'backgroundColor' => [
                        'rgba(14, 165, 233, 0.7)',
                        'rgba(34, 197, 94, 0.7)',
                        'rgba(168, 85, 247, 0.7)',
                        'rgba(245, 158, 11, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                    ],
                    'borderColor' => [
                        'rgb(14, 165, 233)',
                        'rgb(34, 197, 94)',
                        'rgb(168, 85, 247)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                    ],
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $customers->pluck('name')->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => "function(value) { return new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(value); }",
                    ],
                ],
            ],
            'plugins' => [
                'legend' => ['display' => false],
            ],
        ];
    }
}
