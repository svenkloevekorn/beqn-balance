<?php

namespace App\Filament\Resources\DeliveryNotes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DeliveryNotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('delivery_note_number')
                    ->label('Lieferscheinnr.')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Kunde')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('delivery_date')
                    ->label('Lieferdatum')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Entwurf',
                        'sent' => 'Versendet',
                        'delivered' => 'Zugestellt',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'warning',
                        'delivered' => 'success',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('delivery_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Entwurf',
                        'sent' => 'Versendet',
                        'delivered' => 'Zugestellt',
                    ]),
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
