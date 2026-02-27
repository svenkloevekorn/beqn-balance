<?php

namespace App\Filament\Resources\Articles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
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
                TextColumn::make('categories.name')
                    ->label('Kategorien')
                    ->badge()
                    ->separator(',')
                    ->searchable(),
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
                IconColumn::make('is_active')
                    ->label('Aktiv')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Aktiv')
                    ->trueLabel('Nur aktive')
                    ->falseLabel('Nur inaktive')
                    ->placeholder('Alle'),
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
