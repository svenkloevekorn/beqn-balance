<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Services\PdfService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Query\Builder;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Rechnungsnr.')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Kunde')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('invoice_date')
                    ->label('Datum')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Fällig am')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('net_total')
                    ->label('Netto')
                    ->money('EUR')
                    ->getStateUsing(fn (Invoice $record) => $record->net_total)
                    ->summarize(
                        Summarizer::make()
                            ->label('Gesamt')
                            ->using(function (Builder $query): float {
                                $ids = $query->pluck('id');

                                return InvoiceItem::whereIn('invoice_id', $ids)
                                    ->selectRaw('SUM(quantity * net_price) as total')
                                    ->value('total') ?? 0;
                            })
                            ->money('EUR')
                    ),
                TextColumn::make('remaining_amount')
                    ->label('Offen')
                    ->money('EUR')
                    ->getStateUsing(fn (Invoice $record) => $record->remaining_amount)
                    ->summarize(
                        Summarizer::make()
                            ->label('Gesamt')
                            ->using(function (Builder $query): float {
                                $invoices = Invoice::whereIn('id', $query->pluck('id'))
                                    ->with(['items', 'payments'])
                                    ->get();

                                return $invoices->sum(fn (Invoice $inv) => $inv->remaining_amount);
                            })
                            ->money('EUR')
                    ),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->defaultSort('invoice_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(InvoiceStatus::class),
                SelectFilter::make('year')
                    ->label('Jahr')
                    ->options(fn () => Invoice::selectRaw('YEAR(invoice_date) as year')
                        ->distinct()
                        ->orderByDesc('year')
                        ->pluck('year', 'year')
                        ->mapWithKeys(fn ($year) => [$year => $year])
                        ->toArray()
                    )
                    ->query(fn ($query, array $data) => $query->when(
                        $data['value'],
                        fn ($q, $year) => $q->whereYear('invoice_date', $year),
                    )),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('addPayment')
                    ->label('Zahlung erfassen')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->color('success')
                    ->form([
                        DatePicker::make('payment_date')
                            ->label('Datum')
                            ->default(now())
                            ->required(),
                        TextInput::make('amount')
                            ->label('Betrag (€)')
                            ->numeric()
                            ->step(0.01)
                            ->required(),
                        Select::make('payment_method')
                            ->label('Zahlungsart')
                            ->options(PaymentMethod::class)
                            ->default(PaymentMethod::BankTransfer)
                            ->required(),
                        TextInput::make('notes')
                            ->label('Bemerkung'),
                    ])
                    ->action(function (Invoice $record, array $data) {
                        $record->payments()->create($data);

                        Notification::make()
                            ->title('Zahlung erfasst')
                            ->success()
                            ->send();
                    })
                    ->hidden(fn (Invoice $record) => in_array($record->status, [
                        InvoiceStatus::Draft,
                        InvoiceStatus::Paid,
                        InvoiceStatus::Cancelled,
                    ])),
                Action::make('downloadPdf')
                    ->label(fn ($record) => $record->status === InvoiceStatus::Draft ? 'Vorschau' : 'PDF')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color(fn ($record) => $record->status === InvoiceStatus::Draft ? 'gray' : 'success')
                    ->action(function ($record) {
                        $service = app(PdfService::class);
                        $isDraft = $record->status === InvoiceStatus::Draft;
                        $content = $service->generateInvoice($record, $isDraft);
                        $filename = $record->invoice_number . ($isDraft ? '_ENTWURF' : '') . '.pdf';

                        return response()->streamDownload(fn () => print($content), $filename, [
                            'Content-Type' => 'application/pdf',
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
