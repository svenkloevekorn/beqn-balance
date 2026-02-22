<?php

namespace App\Filament\Resources\IncomingInvoices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class IncomingInvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('supplier.name')
                    ->label('Lieferant')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('external_invoice_number')
                    ->label('Ext. Rechnungsnr.')
                    ->searchable(),
                TextColumn::make('invoice_date')
                    ->label('Datum')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Fällig am')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('gross_amount')
                    ->label('Brutto')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'Offen',
                        'paid' => 'Bezahlt',
                        'cancelled' => 'Storniert',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('invoice_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Offen',
                        'paid' => 'Bezahlt',
                        'cancelled' => 'Storniert',
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
