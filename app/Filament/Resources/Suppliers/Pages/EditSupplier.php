<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Filament\Resources\Suppliers\SupplierResource;
use Filament\Actions\DeleteAction;
use App\Filament\Concerns\RedirectsToListPage;
use Filament\Resources\Pages\EditRecord;

class EditSupplier extends EditRecord
{
    use RedirectsToListPage;
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
