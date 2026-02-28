<?php

namespace App\Filament\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Article;
use Filament\Widgets\ChartWidget;

class ArticleStatsWidget extends ChartWidget
{
    protected static ?int $sort = -4;

    protected int | string | array $columnSpan = 1;

    protected ?string $heading = 'Top 5 Artikel — Umsatzverteilung';

    protected ?string $description = 'Anteil am Gesamtumsatz';

    protected ?string $maxHeight = '300px';

    protected ?string $pollingInterval = null;

    protected function getType(): string
    {
        return 'doughnut';
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

        $articles = Article::query()
            ->select('articles.id', 'articles.name')
            ->selectSub(function ($query) use ($statuses, $year) {
                $query->from('invoice_items')
                    ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
                    ->whereColumn('invoice_items.article_id', 'articles.id')
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
                    'data' => $articles->pluck('umsatz')->map(fn ($v) => round($v, 2))->toArray(),
                    'backgroundColor' => [
                        'rgba(14, 165, 233, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                    ],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $articles->pluck('name')->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'padding' => 16,
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                    ],
                ],
            ],
            'cutout' => '60%',
        ];
    }
}
