<?php

namespace App\Filament\Resources\DeliveryNotes\Pages;

use App\Enums\DeliveryNoteStatus;
use App\Filament\Resources\DeliveryNotes\DeliveryNoteResource;
use App\Models\DeliveryNote;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListDeliveryNotes extends ListRecords
{
    protected static string $resource = DeliveryNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'alle' => Tab::make('Alle')
                ->badge(DeliveryNote::count()),
        ];

        foreach (DeliveryNoteStatus::cases() as $status) {
            $tabs[$status->value] = Tab::make($status->getLabel())
                ->modifyQueryUsing(fn ($query) => $query->where('status', $status))
                ->badge(DeliveryNote::where('status', $status)->count());
        }

        return $tabs;
    }
}
