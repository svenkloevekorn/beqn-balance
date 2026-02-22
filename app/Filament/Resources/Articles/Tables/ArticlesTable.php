<?php

namespace App\Filament\Resources\Articles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Bezeichnung')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit')
                    ->label('Einheit')
                    ->sortable(),
                TextColumn::make('net_price')
                    ->label('Nettopreis')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('vat_rate')
                    ->label('MwSt')
                    ->suffix(' %')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
