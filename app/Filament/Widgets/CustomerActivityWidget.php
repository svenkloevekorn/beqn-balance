<?php

namespace App\Filament\Widgets;

use App\Enums\InvoiceStatus;
use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class CustomerActivityWidget extends TableWidget
{
    protected static ?int $sort = -6;

    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'Aktivste Kunden';

    public function table(Table $table): Table
    {
        $statuses = [InvoiceStatus::Paid->value, InvoiceStatus::PartiallyPaid->value];
        $threeMonthsAgo = now()->subMonths(3);

        return $table
            ->query(
                Customer::query()
                    ->withCount('invoices')
                    ->selectSub(function ($query) use ($statuses, $threeMonthsAgo) {
                        $query->from('invoice_items')
                            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
                            ->whereColumn('invoices.customer_id', 'customers.id')
                            ->whereIn('invoices.status', $statuses)
                            ->where('invoices.invoice_date', '>=', $threeMonthsAgo)
                            ->selectRaw('COALESCE(SUM(invoice_items.quantity * invoice_items.net_price * (1 + invoice_items.vat_rate / 100)), 0)');
                    }, 'umsatz_3m')
                    ->having('invoices_count', '>', 0)
                    ->orderByDesc('invoices_count')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('row_number')
                    ->label('#')
                    ->rowIndex()
                    ->width('1%'),
                TextColumn::make('name')
                    ->label('Kunde')
                    ->weight('bold'),
                TextColumn::make('invoices_count')
                    ->label('Rechnungen')
                    ->badge()
                    ->color('info')
                    ->alignEnd(),
                TextColumn::make('umsatz_3m')
                    ->label('Umsatz 3 Mon.')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, ',', '.') . ' €')
                    ->badge()
                    ->color('success')
                    ->alignEnd(),
            ])
            ->paginated(false)
            ->striped()
            ->recordUrl(fn (Customer $record) => CustomerResource::getUrl('edit', ['record' => $record]));
    }
}
