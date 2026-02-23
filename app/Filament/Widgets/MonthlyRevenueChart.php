<?php

namespace App\Filament\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Filament\Widgets\BarChartWidget;
use Illuminate\Support\Carbon;

class MonthlyRevenueChart extends BarChartWidget
{
    protected static ?int $sort = -2;

    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = 'Umsatz der letzten 12 Monate';

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->translatedFormat('M Y');

            $revenue = Invoice::where('status', InvoiceStatus::Paid)
                ->whereMonth('invoice_date', $date->month)
                ->whereYear('invoice_date', $date->year)
                ->get()
                ->sum(fn (Invoice $inv) => $inv->gross_total);

            $data[] = round($revenue, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Umsatz (€)',
                    'data' => $data,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.6)',
                    'borderColor' => 'rgb(245, 158, 11)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
