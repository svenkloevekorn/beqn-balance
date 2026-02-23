<?php

namespace App\Filament\Widgets;

use App\Enums\InvoiceStatus;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestInvoicesWidget extends TableWidget
{
    protected static ?int $sort = -1;

    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'Neueste Rechnungen';

    public function table(Table $table): Table
    {
        return $table
            ->query(Invoice::query()->latest('invoice_date')->limit(5))
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Nr.'),
                TextColumn::make('customer.name')
                    ->label('Kunde'),
                TextColumn::make('invoice_date')
                    ->label('Datum')
                    ->date('d.m.Y'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->paginated(false)
            ->recordUrl(fn (Invoice $record) => InvoiceResource::getUrl('edit', ['record' => $record]));
    }
}
