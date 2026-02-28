<?php

namespace App\Filament\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Article;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class ArticleRevenueTableWidget extends TableWidget
{
    protected static ?int $sort = -3;

    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'Top 5 Artikel — Details';

    public function table(Table $table): Table
    {
        $year = now()->year;
        $statuses = [InvoiceStatus::Paid->value, InvoiceStatus::PartiallyPaid->value];

        return $table
            ->query(
                Article::query()
                    ->select('articles.id', 'articles.name', 'articles.unit')
                    ->selectSub(function ($query) use ($statuses) {
                        $query->from('invoice_items')
                            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
                            ->whereColumn('invoice_items.article_id', 'articles.id')
                            ->whereIn('invoices.status', $statuses)
                            ->selectRaw('COALESCE(SUM(invoice_items.quantity), 0)');
                    }, 'menge_gesamt')
                    ->selectSub(function ($query) use ($statuses, $year) {
                        $query->from('invoice_items')
                            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
                            ->whereColumn('invoice_items.article_id', 'articles.id')
                            ->whereIn('invoices.status', $statuses)
                            ->whereYear('invoices.invoice_date', $year)
                            ->selectRaw('COALESCE(SUM(invoice_items.quantity * invoice_items.net_price * (1 + invoice_items.vat_rate / 100)), 0)');
                    }, 'umsatz_jahr')
                    ->selectSub(function ($query) use ($statuses) {
                        $query->from('invoice_items')
                            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
                            ->whereColumn('invoice_items.article_id', 'articles.id')
                            ->whereIn('invoices.status', $statuses)
                            ->selectRaw('COALESCE(SUM(invoice_items.quantity * invoice_items.net_price * (1 + invoice_items.vat_rate / 100)), 0)');
                    }, 'umsatz_gesamt')
                    ->having('umsatz_gesamt', '>', 0)
                    ->orderByDesc('umsatz_gesamt')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('row_number')
                    ->label('#')
                    ->rowIndex()
                    ->width('1%'),
                TextColumn::make('name')
                    ->label('Artikel')
                    ->weight('bold'),
                TextColumn::make('menge_gesamt')
                    ->label('Menge')
                    ->formatStateUsing(fn ($state, $record) => number_format($state, 0, ',', '.') . ' ' . $record->unit)
                    ->badge()
                    ->color('info')
                    ->alignEnd(),
                TextColumn::make('umsatz_jahr')
                    ->label('Umsatz ' . now()->year)
                    ->formatStateUsing(fn ($state) => number_format($state, 2, ',', '.') . ' €')
                    ->badge()
                    ->color('success')
                    ->alignEnd(),
                TextColumn::make('umsatz_gesamt')
                    ->label('Umsatz Gesamt')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, ',', '.') . ' €')
                    ->badge()
                    ->color('primary')
                    ->alignEnd(),
            ])
            ->paginated(false)
            ->striped();
    }
}
