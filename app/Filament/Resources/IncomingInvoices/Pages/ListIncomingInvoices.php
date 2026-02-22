<?php

namespace App\Filament\Resources\IncomingInvoices\Pages;

use App\Filament\Resources\IncomingInvoices\IncomingInvoiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIncomingInvoices extends ListRecords
{
    protected static string $resource = IncomingInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
