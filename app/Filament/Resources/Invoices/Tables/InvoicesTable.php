<?php

namespace App\Filament\Resources\Invoices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Rechnungsnr.')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Kunde')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('invoice_date')
                    ->label('Datum')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Fällig am')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Entwurf',
                        'sent' => 'Versendet',
                        'paid' => 'Bezahlt',
                        'cancelled' => 'Storniert',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'warning',
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
                        'draft' => 'Entwurf',
                        'sent' => 'Versendet',
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
