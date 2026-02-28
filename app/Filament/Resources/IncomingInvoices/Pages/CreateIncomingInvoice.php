<?php

namespace App\Filament\Resources\IncomingInvoices\Pages;

use App\Filament\Resources\IncomingInvoices\IncomingInvoiceResource;
use App\Filament\Concerns\RedirectsToListPage;
use Filament\Resources\Pages\CreateRecord;

class CreateIncomingInvoice extends CreateRecord
{
    use RedirectsToListPage;
    protected static string $resource = IncomingInvoiceResource::class;
}
