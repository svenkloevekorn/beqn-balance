<?php

namespace App\Filament\Resources\DeliveryNotes\Pages;

use App\Filament\Resources\DeliveryNotes\DeliveryNoteResource;
use App\Models\NumberRange;
use App\Filament\Concerns\RedirectsToListPage;
use Filament\Resources\Pages\CreateRecord;

class CreateDeliveryNote extends CreateRecord
{
    use RedirectsToListPage;
    protected static string $resource = DeliveryNoteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['delivery_note_number'] = NumberRange::generateNext('delivery_note');

        return $data;
    }
}
