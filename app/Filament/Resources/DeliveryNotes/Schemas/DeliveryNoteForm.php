<?php

namespace App\Filament\Resources\DeliveryNotes\Schemas;

use App\Enums\DeliveryNoteStatus;
use App\Models\Article;
use App\Models\Customer;
use App\Models\CustomerArticlePrice;
use App\Models\NumberRange;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                        if ($state !== null && $state !== '') {
                                            return;
                                        }
                                        $articleId = $get('article_id');
                                        if (! $articleId) {
                                            return;
                                        }
                                        $article = Article::find($articleId);
                                        if (! $article) {
                                            return;
                                        }
                                        $customerId = $get('../../customer_id');
                                        if ($customerId) {
                                            $customer = Customer::find($customerId);
                                            if ($customer && $customer->pricing_mode === 'custom_prices') {
                                                $cap = CustomerArticlePrice::where('customer_id', $customerId)
                                                    ->where('article_id', $articleId)
                                                    ->where('is_active', true)
                                                    ->whereNotNull('custom_net_price')
                                                    ->first();
                                                if ($cap) {
                                                    $set('net_price', $cap->custom_net_price);
                                                    return;
                                                }
                                            }
                                        }
                                        $set('net_price', $article->net_price);
                                    })
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
                                Placeholder::make('custom_price_badge')
                                    ->hiddenLabel()
                                    ->content(function (Get $get): HtmlString {
                                        $articleId = $get('article_id');
                                        $customerId = $get('../../customer_id');
                                        if (! $articleId || ! $customerId) {
                                            return new HtmlString('');
                                        }
                                        $customer = Customer::find($customerId);
                                        if (! $customer || $customer->pricing_mode !== 'custom_prices') {
                                            return new HtmlString('');
                                        }
                                        $cap = CustomerArticlePrice::where('customer_id', $customerId)
                                            ->where('article_id', $articleId)
                                            ->where('is_active', true)
                                            ->whereNotNull('custom_net_price')
                                            ->first();
                                        if (! $cap) {
                                            return new HtmlString('');
                                        }
                                        $article = Article::find($articleId);
                                        $listPrice = $article ? number_format((float) $article->net_price, 2, ',', '.') : '–';
                                        return new HtmlString(
                                            '<span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; background: #dbeafe; border: 1px solid #93c5fd; border-radius: 9999px; font-size: 12px; color: #1e40af;">'
                                            . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 14px; height: 14px;"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" /></svg>'
                                            . 'Kundenpreis (Listenpreis: ' . $listPrice . ' &euro;)'
                                            . '</span>'
                                        );
                                    })
                                    ->columnSpanFull(),
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
