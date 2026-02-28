<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Models\Customer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('city')
                    ->label('Ort')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->label('E-Mail')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('payment_term_days')
                    ->label('Zahlungsziel')
                    ->suffix(' Tage')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('pricing_mode')
                    ->label('Preismodus')
                    ->icon(fn (string $state): string => match ($state) {
                        'percentage' => 'heroicon-o-receipt-percent',
                        'custom_prices' => 'heroicon-o-currency-euro',
                        default => 'heroicon-o-minus',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'warning',
                        'custom_prices' => 'success',
                        default => 'gray',
                    })
                    ->tooltip(fn (string $state): string => match ($state) {
                        'percentage' => 'Prozentualer Rabatt',
                        'custom_prices' => 'Individuelle Preise',
                        default => 'Kein Rabatt',
                    }),
            ])
            ->filters([
                SelectFilter::make('city')
                    ->label('Ort')
                    ->options(fn () => Customer::whereNotNull('city')->where('city', '!=', '')->distinct()->orderBy('city')->pluck('city', 'city')->toArray())
                    ->searchable(),
                SelectFilter::make('zip')
                    ->label('PLZ')
                    ->options(fn () => Customer::whereNotNull('zip')->where('zip', '!=', '')->distinct()->orderBy('zip')->pluck('zip', 'zip')->toArray())
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(25);
    }
}
