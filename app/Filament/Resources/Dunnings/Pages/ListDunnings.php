<?php

namespace App\Filament\Resources\Dunnings\Pages;

use App\Enums\DunningLevel;
use App\Filament\Resources\Dunnings\DunningResource;
use App\Models\Dunning;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListDunnings extends ListRecords
{
    protected static string $resource = DunningResource::class;

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
                ->badge(Dunning::count()),
        ];

        foreach (DunningLevel::cases() as $level) {
            $tabs[$level->value] = Tab::make($level->getLabel())
                ->modifyQueryUsing(fn ($query) => $query->where('level', $level))
                ->badge(Dunning::where('level', $level)->count());
        }

        return $tabs;
    }
}
