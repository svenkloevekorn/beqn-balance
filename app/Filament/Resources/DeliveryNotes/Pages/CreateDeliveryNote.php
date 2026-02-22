<?php

namespace App\Filament\Resources\DeliveryNotes\Pages;

use App\Filament\Resources\DeliveryNotes\DeliveryNoteResource;
use App\Models\NumberRange;
use Filament\Resources\Pages\CreateRecord;

class CreateDeliveryNote extends CreateRecord
{
    protected static string $resource = DeliveryNoteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['delivery_note_number'] = NumberRange::generateNext('delivery_note');

        return $data;
    }
}
