<?php

namespace App\Filament\Resources\Quotes\Tables;

use App\Enums\QuoteStatus;
use App\Filament\Resources\DeliveryNotes\DeliveryNoteResource;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Quote;
use App\Services\PdfService;
use App\Services\QuoteConversionService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quote_number')
                    ->label('Angebotsnr.')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Kunde')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quote_date')
                    ->label('Datum')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('valid_until')
                    ->label('Gueltig bis')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->defaultSort('quote_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(QuoteStatus::class),
                SelectFilter::make('year')
                    ->label('Jahr')
                    ->options(fn () => Quote::selectRaw('YEAR(quote_date) as year')
                        ->distinct()
                        ->orderByDesc('year')
                        ->pluck('year', 'year')
                        ->mapWithKeys(fn ($year) => [$year => $year])
                        ->toArray()
                    )
                    ->query(fn ($query, array $data) => $query->when(
                        $data['value'],
                        fn ($q, $year) => $q->whereYear('quote_date', $year),
                    )),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('convertToInvoice')
                    ->label('In Rechnung')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Angebot in Rechnung umwandeln')
                    ->modalDescription('Es wird eine neue Rechnung mit allen Positionen erstellt.')
                    ->action(function ($record) {
                        $service = app(QuoteConversionService::class);
                        $invoice = $service->toInvoice($record);

                        Notification::make()
                            ->title('Rechnung ' . $invoice->invoice_number . ' erstellt')
                            ->success()
                            ->send();

                        return redirect(InvoiceResource::getUrl('edit', ['record' => $invoice]));
                    }),
                Action::make('convertToDeliveryNote')
                    ->label('In Lieferschein')
                    ->icon(Heroicon::OutlinedTruck)
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Angebot in Lieferschein umwandeln')
                    ->modalDescription('Es wird ein neuer Lieferschein mit allen Positionen erstellt.')
                    ->action(function ($record) {
                        $service = app(QuoteConversionService::class);
                        $deliveryNote = $service->toDeliveryNote($record);

                        Notification::make()
                            ->title('Lieferschein ' . $deliveryNote->delivery_note_number . ' erstellt')
                            ->success()
                            ->send();

                        return redirect(DeliveryNoteResource::getUrl('edit', ['record' => $deliveryNote]));
                    }),
                Action::make('downloadPdf')
                    ->label(fn ($record) => $record->status === QuoteStatus::Draft ? 'Vorschau' : 'PDF')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color(fn ($record) => $record->status === QuoteStatus::Draft ? 'gray' : 'success')
                    ->action(function ($record) {
                        $service = app(PdfService::class);
                        $isDraft = $record->status === QuoteStatus::Draft;
                        $content = $service->generateQuote($record, $isDraft);
                        $filename = $record->quote_number . ($isDraft ? '_ENTWURF' : '') . '.pdf';

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
