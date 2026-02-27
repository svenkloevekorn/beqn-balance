<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Models\Article;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Kunde')
                    ->contained(false)
                    ->persistTabInQueryString()
                    ->columnSpanFull()
                    ->schema([
                        Tab::make('Kundendaten')
                            ->icon(Heroicon::OutlinedUser)
                            ->schema([
                                Section::make('Kontaktdaten')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Name')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('email')
                                            ->label('E-Mail')
                                            ->email()
                                            ->maxLength(255),
                                        TextInput::make('phone')
                                            ->label('Telefon')
                                            ->tel()
                                            ->maxLength(255),
                                        TextInput::make('vat_id')
                                            ->label('USt-IdNr.')
                                            ->maxLength(255),
                                        TextInput::make('buyer_reference')
                                            ->label('Käufer-Referenz (E-Rechnung)')
                                            ->helperText('Wird als Referenz in E-Rechnungen verwendet (z.B. Leitweg-ID bei Behörden)')
                                            ->maxLength(255),
                                    ]),
                                Section::make('Adresse')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('street')
                                            ->label('Straße')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        TextInput::make('zip')
                                            ->label('PLZ')
                                            ->maxLength(10),
                                        TextInput::make('city')
                                            ->label('Ort')
                                            ->maxLength(255),
                                        TextInput::make('country')
                                            ->label('Land')
                                            ->default('DE')
                                            ->maxLength(255),
                                    ]),
                                Section::make('Konditionen')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('payment_term_days')
                                            ->label('Zahlungsziel (Tage)')
                                            ->numeric()
                                            ->required()
                                            ->default(14),
                                        Select::make('pricing_mode')
                                            ->label('Preismodus')
                                            ->options([
                                                'none' => 'Kein Rabatt',
                                                'percentage' => 'Prozentualer Rabatt',
                                                'custom_prices' => 'Individuelle Preise',
                                            ])
                                            ->default('none')
                                            ->required()
                                            ->live(),
                                        TextInput::make('discount_percent')
                                            ->label('Kundenrabatt (%)')
                                            ->numeric()
                                            ->step(0.01)
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->suffix('%')
                                            ->placeholder('z.B. 5.00')
                                            ->visible(fn (Get $get): bool => $get('pricing_mode') === 'percentage'),
                                    ]),
                                Section::make('Notizen')
                                    ->schema([
                                        Textarea::make('notes')
                                            ->label('')
                                            ->rows(4)
                                            ->placeholder('Interne Notizen zu diesem Kunden...')
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsed(),
                            ]),
                        Tab::make('Individuelle Preise')
                            ->icon(Heroicon::OutlinedCurrencyEuro)
                            ->visible(fn (Get $get) => $get('pricing_mode') === 'custom_prices')
                            ->hiddenOn('create')
                            ->schema([
                                Repeater::make('customPrices')
                                    ->label('')
                                    ->relationship()
                                    ->schema([
                                        Hidden::make('article_id'),
                                        Placeholder::make('article_name')
                                            ->label('Artikel')
                                            ->content(fn (Get $get): string => Article::find($get('article_id'))?->name ?? '-'),
                                        Placeholder::make('original_price')
                                            ->label('Standard-Nettopreis')
                                            ->content(fn (Get $get): string => number_format((float) (Article::find($get('article_id'))?->net_price ?? 0), 2, ',', '.') . ' €'),
                                        Toggle::make('is_active')
                                            ->label('Aktiv')
                                            ->inline(false)
                                            ->live(),
                                        TextInput::make('custom_net_price')
                                            ->label('Individueller Nettopreis')
                                            ->numeric()
                                            ->step(0.01)
                                            ->suffix('€')
                                            ->placeholder('Standard-Preis')
                                            ->disabled(fn (Get $get): bool => ! $get('is_active')),
                                    ])
                                    ->columns(4)
                                    ->addable(false)
                                    ->deletable(false)
                                    ->reorderable(false)
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Ansprechpartner')
                            ->icon(Heroicon::OutlinedUsers)
                            ->schema([
                                Repeater::make('contactPersons')
                                    ->label('')
                                    ->relationship()
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Name')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('position')
                                            ->label('Position')
                                            ->maxLength(255),
                                        TextInput::make('email')
                                            ->label('E-Mail')
                                            ->email()
                                            ->maxLength(255),
                                        TextInput::make('phone')
                                            ->label('Telefon')
                                            ->tel()
                                            ->maxLength(255),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(0)
                                    ->addActionLabel('Ansprechpartner hinzufuegen')
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                            ]),
                    ]),
            ]);
    }
}
