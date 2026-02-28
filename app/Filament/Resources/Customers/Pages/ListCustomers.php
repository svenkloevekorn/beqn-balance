<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'alle' => Tab::make('Alle')
                ->badge(fn () => static::getResource()::getModel()::count()),
            'percentage' => Tab::make('Prozentrabatt')
                ->icon('heroicon-o-receipt-percent')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('pricing_mode', 'percentage'))
                ->badge(fn () => static::getResource()::getModel()::where('pricing_mode', 'percentage')->count()),
            'custom_prices' => Tab::make('Individuelle Preise')
                ->icon('heroicon-o-currency-euro')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('pricing_mode', 'custom_prices'))
                ->badge(fn () => static::getResource()::getModel()::where('pricing_mode', 'custom_prices')->count()),
            'none' => Tab::make('Kein Rabatt')
                ->icon('heroicon-o-minus')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('pricing_mode', 'none'))
                ->badge(fn () => static::getResource()::getModel()::where('pricing_mode', 'none')->count()),
        ];
    }
}
