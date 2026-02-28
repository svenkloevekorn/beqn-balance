<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Filament\Resources\Suppliers\SupplierResource;
use App\Filament\Concerns\RedirectsToListPage;
use Filament\Resources\Pages\CreateRecord;

class CreateSupplier extends CreateRecord
{
    use RedirectsToListPage;
    protected static string $resource = SupplierResource::class;
}
