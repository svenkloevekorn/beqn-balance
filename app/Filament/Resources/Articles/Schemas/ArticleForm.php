<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;

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
                Select::make('vat_rate')
                    ->label('MwSt-Satz (%)')
                    ->options([
                        '19.00' => '19 %',
                        '7.00' => '7 %',
                        '0.00' => '0 %',
                    ])
                    ->default('19.00')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $net = (float) $get('net_price');
                        $vat = (float) ($state ?? 0);
                        $set('gross_price', number_format(round($net * (1 + $vat / 100), 2), 2, '.', ''));
                    }),
                TextInput::make('net_price')
                    ->label('Nettopreis (€)')
                    ->numeric()
                    ->step(0.01)
                    ->required()
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $net = (float) ($state ?? 0);
                        $vat = (float) ($get('vat_rate') ?? 0);
                        $set('gross_price', number_format(round($net * (1 + $vat / 100), 2), 2, '.', ''));
                    }),
                TextInput::make('gross_price')
                    ->label('Bruttopreis (€)')
                    ->numeric()
                    ->step(0.01)
                    ->default(0)
                    ->dehydrated(false)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $gross = (float) ($state ?? 0);
                        $vat = (float) ($get('vat_rate') ?? 0);
                        $net = round($gross / (1 + $vat / 100), 2);
                        $set('net_price', number_format($net, 2, '.', ''));
                    })
                    ->afterStateHydrated(function (TextInput $component, callable $get) {
                        $net = (float) ($get('net_price') ?? 0);
                        $vat = (float) ($get('vat_rate') ?? 0);
                        $component->state(number_format(round($net * (1 + $vat / 100), 2), 2, '.', ''));
                    }),
            ]);
    }
}
