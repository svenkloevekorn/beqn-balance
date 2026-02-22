<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-Mail')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role.name')
                    ->label('Rolle')
                    ->badge()
                    ->sortable()
                    ->placeholder('Keine Rolle'),
                IconColumn::make('is_super_admin')
                    ->label('Super-Admin')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Erstellt am')
                    ->dateTime('d.m.Y')
                    ->sortable(),
            ])
            ->defaultSort('name');
    }
}
