<?php

namespace App\Filament\Resources\Quotes\Schemas;

use App\Models\Article;
use App\Models\Customer;
use App\Models\NumberRange;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Angebotsdaten')
                    ->columns(2)
                    ->schema([
                        TextInput::make('quote_number')
                            ->label('Angebotsnummer')
                            ->default(fn () => NumberRange::where('type', 'quote')->first()?->previewNext() ?? 'AN-0001')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Select::make('customer_id')
                            ->label('Kunde')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if (! $state) {
                                    return;
                                }
                                $customer = Customer::find($state);
                                if ($customer) {
                                    $set('discount_percent', $customer->discount_percent);
                                    $set('apply_discount', (bool) $customer->discount_percent);
                                }
                            })
                            ->live(),
                        DatePicker::make('quote_date')
                            ->label('Angebotsdatum')
                            ->default(now())
                            ->required(),
                        DatePicker::make('valid_until')
                            ->label('Gueltig bis')
                            ->default(now()->addDays(30)),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Entwurf',
                                'sent' => 'Versendet',
                                'accepted' => 'Angenommen',
                                'rejected' => 'Abgelehnt',
                            ])
                            ->default('draft')
                            ->required(),
                    ]),
                Section::make('Rabatt')
                    ->columns(2)
                    ->schema([
                        Checkbox::make('apply_discount')
                            ->label('Kundenrabatt anwenden')
                            ->default(true)
                            ->live()
                            ->columnSpanFull(),
                        TextInput::make('discount_percent')
                            ->label('Rabatt (%)')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->visible(fn (callable $get) => $get('apply_discount')),
                    ]),
                Section::make('Positionen')
                    ->schema([
                        Repeater::make('items')
                            ->label('')
                            ->relationship()
                            ->schema([
                                Select::make('article_id')
                                    ->label('Artikel')
                                    ->options(Article::pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        if (! $state) {
                                            return;
                                        }
                                        $article = Article::find($state);
                                        if ($article) {
                                            $set('description', $article->name);
                                            $set('net_price', $article->net_price);
                                            $set('vat_rate', $article->vat_rate);
                                            $set('unit', $article->unit);
                                        }
                                    })
                                    ->live()
                                    ->columnSpan(2),
                                TextInput::make('description')
                                    ->label('Beschreibung')
                                    ->required()
                                    ->columnSpan(3),
                                TextInput::make('quantity')
                                    ->label('Menge')
                                    ->numeric()
                                    ->step(0.01)
                                    ->default(1)
                                    ->required()
                                    ->columnSpan(1),
                                TextInput::make('unit')
                                    ->label('Einheit')
                                    ->default('Stück')
                                    ->required()
                                    ->columnSpan(1),
                                TextInput::make('net_price')
                                    ->label('Nettopreis (€)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->default(0)
                                    ->required()
                                    ->columnSpan(1),
                                Select::make('vat_rate')
                                    ->label('MwSt (%)')
                                    ->options([
                                        '19.00' => '19 %',
                                        '7.00' => '7 %',
                                        '0.00' => '0 %',
                                    ])
                                    ->default('19.00')
                                    ->required()
                                    ->columnSpan(1),
                                Hidden::make('sort_order')
                                    ->default(0),
                            ])
                            ->columns(9)
                            ->defaultItems(1)
                            ->reorderable()
                            ->reorderableWithButtons()
                            ->addActionLabel('Position hinzufuegen')
                            ->columnSpanFull(),
                    ]),
                Section::make('Bemerkungen')
                    ->schema([
                        Textarea::make('notes')
                            ->label('')
                            ->rows(3)
                            ->placeholder('Bemerkungen zum Angebot...')
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }
}
