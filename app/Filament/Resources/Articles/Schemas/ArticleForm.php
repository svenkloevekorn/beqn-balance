<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Bezeichnung')
                    ->required()
                    ->maxLength(255),
                Select::make('categories')
                    ->label('Kategorien')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->required()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->unique()
                            ->maxLength(255),
                    ]),
                Textarea::make('description')
                    ->label('Beschreibung')
                    ->rows(3)
                    ->columnSpanFull(),
                Select::make('unit')
                    ->label('Einheit')
                    ->options([
                        'Stück' => 'Stück',
                        'Stunde' => 'Stunde',
                        'Pauschal' => 'Pauschal',
                        'kg' => 'kg',
                        'm' => 'm',
                        'm²' => 'm²',
                    ])
                    ->default('Stück')
                    ->required(),
                TextInput::make('net_price')
                    ->label('Nettopreis (€)')
                    ->numeric()
                    ->step(0.01)
                    ->required()
                    ->default(0),
                Select::make('vat_rate')
                    ->label('MwSt-Satz (%)')
                    ->options([
                        '19.00' => '19 %',
                        '7.00' => '7 %',
                        '0.00' => '0 %',
                    ])
                    ->default('19.00')
                    ->required(),
            ]);
    }
}
