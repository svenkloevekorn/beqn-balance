<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Enums\InvoiceStatus;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Services\PdfService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label(fn () => $this->record->status === InvoiceStatus::Draft ? 'Vorschau PDF' : 'PDF herunterladen')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color(fn () => $this->record->status === InvoiceStatus::Draft ? 'gray' : 'success')
                ->action(function () {
                    $service = app(PdfService::class);
                    $isDraft = $this->record->status === InvoiceStatus::Draft;
                    $content = $service->generateInvoice($this->record, $isDraft);
                    $filename = $this->record->invoice_number . ($isDraft ? '_ENTWURF' : '') . '.pdf';

                    return response()->streamDownload(fn () => print($content), $filename, [
                        'Content-Type' => 'application/pdf',
                    ]);
                }),
            DeleteAction::make(),
        ];
    }
}
