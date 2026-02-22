<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Echte Nummer erst beim Speichern vergeben (nicht bei Vorschau)
        $data['invoice_number'] = Invoice::generateInvoiceNumber();

        return $data;
    }
}
