<?php

namespace App\Filament\Resources\Quotes\Schemas;

use App\Enums\QuoteStatus;
use App\Models\Article;
use App\Models\Customer;
use App\Models\CustomerArticlePrice;
use App\Models\NumberRange;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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
                                    if ($customer->pricing_mode === 'percentage' && $customer->discount_percent > 0) {
                                        $set('discount_percent', $customer->discount_percent);
                                        $set('apply_discount', true);
                                    } else {
                                        $set('apply_discount', false);
                                        $set('discount_percent', null);
                                    }
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
                            ->options(QuoteStatus::class)
                            ->default(QuoteStatus::Draft)
                            ->required(),
                    ]),
                Section::make('Individuelle Preise')
                    ->icon('heroicon-o-information-circle')
                    ->visible(function (Get $get): bool {
                        $customerId = $get('customer_id');
                        if (! $customerId) {
                            return false;
                        }
                        $customer = Customer::find($customerId);

                        return (bool) ($customer?->pricing_mode === 'custom_prices');
                    })
                    ->schema([
                        Placeholder::make('custom_prices_hint')
                            ->label('')
                            ->content(fn (Get $get): HtmlString => new HtmlString(
                                '<div style="padding: 8px 12px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; color: #1e40af;">'
                                . '<strong>Dieser Kunde hat individuelle Artikelpreise.</strong><br>'
                                . 'Beim Hinzufuegen von Artikeln werden automatisch die hinterlegten Kundenpreise verwendet. '
                                . 'Der prozentuale Kundenrabatt wird nicht angewendet.'
                                . '</div>'
                            )),
                    ]),
                Section::make('Rabatt')
                    ->columns(2)
                    ->visible(function (Get $get): bool {
                        $customerId = $get('customer_id');
                        if (! $customerId) {
                            return true;
                        }
                        $customer = Customer::find($customerId);

                        return ! ($customer?->pricing_mode === 'custom_prices');
                    })
                    ->schema([
                        Checkbox::make('apply_discount')
                            ->label('Kundenrabatt anwenden')
                            ->default(true)
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, ?bool $state) {
                                $items = $get('items') ?? [];
                                $discountPercent = (float) ($get('discount_percent') ?? 0);
                                foreach ($items as $key => $item) {
                                    $articleId = $item['article_id'] ?? null;
                                    if (! $articleId) {
                                        continue;
                                    }
                                    $article = Article::find($articleId);
                                    if (! $article) {
                                        continue;
                                    }
                                    $listPrice = $article->net_price;
                                    $customerId = $get('customer_id');
                                    if ($customerId) {
                                        $customer = Customer::find($customerId);
                                        if ($customer && $customer->pricing_mode === 'custom_prices') {
                                            $cap = CustomerArticlePrice::where('customer_id', $customerId)
                                                ->where('article_id', $articleId)
                                                ->where('is_active', true)
                                                ->whereNotNull('custom_net_price')
                                                ->first();
                                            if ($cap) {
                                                $listPrice = $cap->custom_net_price;
                                            }
                                        }
                                    }
                                    if ($state && $discountPercent > 0) {
                                        $set("items.{$key}.net_price", round($listPrice * (1 - $discountPercent / 100), 2));
                                    } else {
                                        $set("items.{$key}.net_price", $listPrice);
                                    }
                                }
                            })
                            ->columnSpanFull(),
                        TextInput::make('discount_percent')
                            ->label('Rabatt (%)')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                $applyDiscount = $get('apply_discount');
                                $discountPercent = (float) ($state ?? 0);
                                $items = $get('items') ?? [];
                                foreach ($items as $key => $item) {
                                    $articleId = $item['article_id'] ?? null;
                                    if (! $articleId) {
                                        continue;
                                    }
                                    $article = Article::find($articleId);
                                    if (! $article) {
                                        continue;
                                    }
                                    $listPrice = $article->net_price;
                                    $customerId = $get('customer_id');
                                    if ($customerId) {
                                        $customer = Customer::find($customerId);
                                        if ($customer && $customer->pricing_mode === 'custom_prices') {
                                            $cap = CustomerArticlePrice::where('customer_id', $customerId)
                                                ->where('article_id', $articleId)
                                                ->where('is_active', true)
                                                ->whereNotNull('custom_net_price')
                                                ->first();
                                            if ($cap) {
                                                $listPrice = $cap->custom_net_price;
                                            }
                                        }
                                    }
                                    if ($applyDiscount && $discountPercent > 0) {
                                        $set("items.{$key}.net_price", round($listPrice * (1 - $discountPercent / 100), 2));
                                    } else {
                                        $set("items.{$key}.net_price", $listPrice);
                                    }
                                }
                            })
                            ->visible(fn (callable $get) => $get('apply_discount')),
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
                                            $set('description', $article->description);
                                            $set('vat_rate', $article->vat_rate);
                                            $set('unit', $article->unit);

                                            $customerId = $get('../../customer_id');
                                            $listPrice = $article->net_price;
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

                                            $basePrice = $customPrice ?? $listPrice;

                                            $applyDiscount = $get('../../apply_discount');
                                            $discountPercent = (float) ($get('../../discount_percent') ?? 0);
                                            if ($applyDiscount && $discountPercent > 0) {
                                                $set('net_price', round($basePrice * (1 - $discountPercent / 100), 2));
                                            } else {
                                                $set('net_price', $basePrice);
                                            }
                                        }
                                    })
                                    ->live()
                                    ->columnSpan(2),
                                TextInput::make('description')
                                    ->label('Beschreibung')
                                    ->required()
                                    ->columnSpan(fn (Get $get): int => ($get('../../apply_discount') && ((float) ($get('../../discount_percent') ?? 0)) > 0) ? 2 : 3),
                                TextInput::make('quantity')
                                    ->label('Menge')
                                    ->numeric()
                                    ->step(0.01)
                                    ->default(1)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->columnSpan(1),
                                TextInput::make('unit')
                                    ->label('Einheit')
                                    ->default('Stück')
                                    ->required()
                                    ->columnSpan(1),
                                Placeholder::make('list_price_display')
                                    ->label('Listenpreis')
                                    ->content(function (Get $get): HtmlString {
                                        $articleId = $get('article_id');
                                        if (! $articleId) {
                                            return new HtmlString('–');
                                        }
                                        $article = Article::find($articleId);
                                        if (! $article) {
                                            return new HtmlString('–');
                                        }
                                        $listPrice = number_format((float) $article->net_price, 2, ',', '.');
                                        return new HtmlString(
                                            '<span style="text-decoration: line-through; color: #9ca3af;">' . $listPrice . ' &euro;</span>'
                                        );
                                    })
                                    ->visible(fn (Get $get): bool => (bool) $get('../../apply_discount') && ((float) ($get('../../discount_percent') ?? 0)) > 0)
                                    ->columnSpan(1),
                                Placeholder::make('discount_badge')
                                    ->label('Rabatt')
                                    ->content(function (Get $get): HtmlString {
                                        $discountPercent = (float) ($get('../../discount_percent') ?? 0);
                                        if ($discountPercent <= 0) {
                                            return new HtmlString('');
                                        }
                                        return new HtmlString(
                                            '<span style="display: inline-flex; align-items: center; padding: 2px 8px; background: #fef2f2; border: 1px solid #fca5a5; border-radius: 9999px; font-size: 12px; color: #dc2626; font-weight: 600;">'
                                            . '-' . number_format($discountPercent, 2, ',', '.') . ' %'
                                            . '</span>'
                                        );
                                    })
                                    ->visible(fn (Get $get): bool => (bool) $get('../../apply_discount') && ((float) ($get('../../discount_percent') ?? 0)) > 0)
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
                                        $basePrice = $article->net_price;
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
                                                    $basePrice = $cap->custom_net_price;
                                                }
                                            }
                                        }
                                        $applyDiscount = $get('../../apply_discount');
                                        $discountPercent = (float) ($get('../../discount_percent') ?? 0);
                                        if ($applyDiscount && $discountPercent > 0) {
                                            $set('net_price', round($basePrice * (1 - $discountPercent / 100), 2));
                                        } else {
                                            $set('net_price', $basePrice);
                                        }
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
                                    ->live()
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
                            ->columns(fn (Get $get): int => ($get('apply_discount') && ((float) ($get('discount_percent') ?? 0)) > 0) ? 11 : 9)
                            ->defaultItems(1)
                            ->reorderable()
                            ->reorderableWithButtons()
                            ->addActionLabel('Position hinzufuegen')
                            ->columnSpanFull(),
                    ]),
                Section::make('Summen')
                    ->columnSpanFull()
                    ->schema([
                        Placeholder::make('totals')
                            ->label('')
                            ->content(function (Get $get): HtmlString {
                                $items = $get('items') ?? [];

                                $netTotal = 0;
                                $vatGroups = [];

                                foreach ($items as $item) {
                                    $quantity = (float) ($item['quantity'] ?? 0);
                                    $netPrice = (float) ($item['net_price'] ?? 0);
                                    $vatRate = (float) ($item['vat_rate'] ?? 0);

                                    $lineNet = round($quantity * $netPrice, 2);
                                    $netTotal += $lineNet;

                                    $key = number_format($vatRate, 2);
                                    if (! isset($vatGroups[$key])) {
                                        $vatGroups[$key] = 0;
                                    }
                                    $vatGroups[$key] += round($lineNet * $vatRate / 100, 2);
                                }

                                ksort($vatGroups);
                                $vatTotal = array_sum($vatGroups);
                                $grossTotal = $netTotal + $vatTotal;

                                $applyDiscount = $get('apply_discount');
                                $discountPercent = (float) ($get('discount_percent') ?? 0);

                                $fmt = fn ($v) => number_format($v, 2, ',', '.');

                                $html = '<div style="display: flex; justify-content: flex-end;">';
                                $html .= '<table style="min-width: 350px; border-collapse: collapse; font-size: 14px;">';

                                $html .= '<tr><td style="padding: 4px 12px;">Netto-Summe</td>';
                                $html .= '<td style="padding: 4px 12px; text-align: right;">' . $fmt($netTotal) . ' &euro;</td></tr>';

                                if ($applyDiscount && $discountPercent > 0) {
                                    $html .= '<tr><td colspan="2" style="padding: 4px 12px; font-size: 12px; color: #6b7280; font-style: italic;">inkl. ' . $fmt($discountPercent) . ' % Kundenrabatt</td></tr>';
                                }

                                foreach ($vatGroups as $rate => $vat) {
                                    $rateLabel = number_format((float) $rate, 0) . ' %';
                                    $html .= '<tr><td style="padding: 4px 12px;">MwSt ' . $rateLabel . '</td>';
                                    $html .= '<td style="padding: 4px 12px; text-align: right;">' . $fmt($vat) . ' &euro;</td></tr>';
                                }

                                $html .= '<tr style="border-top: 2px solid #d1d5db; font-weight: bold;">';
                                $html .= '<td style="padding: 8px 12px;">Brutto-Summe</td>';
                                $html .= '<td style="padding: 8px 12px; text-align: right;">' . $fmt($grossTotal) . ' &euro;</td></tr>';

                                $html .= '</table></div>';

                                return new HtmlString($html);
                            }),
                    ]),
                Section::make('Bemerkungen')
                    ->columnSpanFull()
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
