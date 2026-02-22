<?php

namespace App\Filament\Resources\IncomingInvoices\Pages;

use App\Filament\Resources\IncomingInvoices\IncomingInvoiceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIncomingInvoice extends EditRecord
{
    protected static string $resource = IncomingInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
