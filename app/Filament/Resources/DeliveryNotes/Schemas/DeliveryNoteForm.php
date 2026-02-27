<?php

namespace App\Filament\Resources\DeliveryNotes\Schemas;

use App\Enums\DeliveryNoteStatus;
use App\Models\Article;
use App\Models\Customer;
use App\Models\CustomerArticlePrice;
use App\Models\NumberRange;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DeliveryNoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Lieferscheindaten')
                    ->columns(2)
                    ->schema([
                        TextInput::make('delivery_note_number')
                            ->label('Lieferscheinnummer')
                            ->default(fn () => NumberRange::where('type', 'delivery_note')->first()?->previewNext() ?? 'LS-0001')
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
                            ->live(),
                        DatePicker::make('delivery_date')
                            ->label('Lieferdatum')
                            ->default(now())
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options(DeliveryNoteStatus::class)
                            ->default(DeliveryNoteStatus::Draft)
                            ->required(),
                    ]),
                Section::make('Positionen')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->label('')
                            ->relationship()
                            ->schema([
                                Select::make('article_id')
                                    ->label('Artikel')
                                    ->options(Article::where('is_active', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                        if (! $state) {
                                            return;
                                        }
                                        $article = Article::find($state);
                                        if ($article) {
                                            $set('description', $article->name);
                                            $set('vat_rate', $article->vat_rate);
                                            $set('unit', $article->unit);

                                            $customerId = $get('../../customer_id');
                                            $customPrice = null;
                                            if ($customerId) {
                                                $customer = Customer::find($customerId);
                                                if ($customer && $customer->pricing_mode === 'custom_prices') {
                                                    $cap = CustomerArticlePrice::where('customer_id', $customerId)
                                                        ->where('article_id', $article->id)
                                                        ->where('is_active', true)
                                                        ->whereNotNull('custom_net_price')
                                                        ->first();
                                                    if ($cap) {
                                                        $customPrice = $cap->custom_net_price;
                                                    }
                                                }
                                            }

                                            $set('net_price', $customPrice ?? $article->net_price);
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
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('notes')
                            ->label('')
                            ->rows(3)
                            ->placeholder('Bemerkungen zum Lieferschein...')
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }
}
