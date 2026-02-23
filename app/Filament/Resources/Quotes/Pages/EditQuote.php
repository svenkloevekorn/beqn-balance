<?php

namespace App\Filament\Resources\Quotes\Pages;

use App\Enums\QuoteStatus;
use App\Filament\Resources\DeliveryNotes\DeliveryNoteResource;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Quotes\QuoteResource;
use App\Services\PdfService;
use App\Services\QuoteConversionService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditQuote extends EditRecord
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('convertToInvoice')
                ->label('In Rechnung umwandeln')
                ->icon(Heroicon::OutlinedDocumentText)
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Angebot in Rechnung umwandeln')
                ->modalDescription('Es wird eine neue Rechnung mit allen Positionen erstellt. Der Angebotsstatus wird auf "Angenommen" gesetzt.')
                ->action(function () {
                    $service = app(QuoteConversionService::class);
                    $invoice = $service->toInvoice($this->record);

                    Notification::make()
                        ->title('Rechnung ' . $invoice->invoice_number . ' erstellt')
                        ->success()
                        ->send();

                    return redirect(InvoiceResource::getUrl('edit', ['record' => $invoice]));
                }),
            Action::make('convertToDeliveryNote')
                ->label('In Lieferschein umwandeln')
                ->icon(Heroicon::OutlinedTruck)
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Angebot in Lieferschein umwandeln')
                ->modalDescription('Es wird ein neuer Lieferschein mit allen Positionen erstellt. Der Angebotsstatus wird auf "Angenommen" gesetzt.')
                ->action(function () {
                    $service = app(QuoteConversionService::class);
                    $deliveryNote = $service->toDeliveryNote($this->record);

                    Notification::make()
                        ->title('Lieferschein ' . $deliveryNote->delivery_note_number . ' erstellt')
                        ->success()
                        ->send();

                    return redirect(DeliveryNoteResource::getUrl('edit', ['record' => $deliveryNote]));
                }),
            Action::make('downloadPdf')
                ->label(fn () => $this->record->status === QuoteStatus::Draft ? 'Vorschau PDF' : 'PDF herunterladen')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color(fn () => $this->record->status === QuoteStatus::Draft ? 'gray' : 'success')
                ->action(function () {
                    $service = app(PdfService::class);
                    $isDraft = $this->record->status === QuoteStatus::Draft;
                    $content = $service->generateQuote($this->record, $isDraft);
                    $filename = $this->record->quote_number . ($isDraft ? '_ENTWURF' : '') . '.pdf';

                    return response()->streamDownload(fn () => print($content), $filename, [
                        'Content-Type' => 'application/pdf',
                    ]);
                }),
            DeleteAction::make(),
        ];
    }
}
