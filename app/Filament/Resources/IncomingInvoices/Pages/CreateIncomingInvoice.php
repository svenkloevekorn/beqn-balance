<?php

namespace App\Filament\Resources\IncomingInvoices\Pages;

use App\Filament\Resources\IncomingInvoices\IncomingInvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIncomingInvoice extends CreateRecord
{
    protected static string $resource = IncomingInvoiceResource::class;
}
