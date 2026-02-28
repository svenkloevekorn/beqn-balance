<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getSubheading(): string|HtmlString|null
    {
        $count = $this->getFilteredTableQuery()->count();

        return new HtmlString(
            '<span style="display:inline-flex;align-items:center;gap:0.25rem;padding:0.25rem 0.625rem;font-size:0.875rem;font-weight:500;border-radius:0.375rem;background:#ecfdf5;color:#059669;border:1px solid rgba(5,150,105,0.2);">'
            . $count . ' Kunden gefunden</span>'
        );
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
