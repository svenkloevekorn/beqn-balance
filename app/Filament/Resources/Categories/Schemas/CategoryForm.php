<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                ColorPicker::make('color')
                    ->label('Farbe'),
                Textarea::make('description')
                    ->label('Beschreibung')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
