<?php

namespace App\Filament\Resources\Roles\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Rolle')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('users_count')
                    ->label('Benutzer')
                    ->counts('users')
                    ->sortable(),
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
