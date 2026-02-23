<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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
                                        TextInput::make('discount_percent')
                                            ->label('Kundenrabatt (%)')
                                            ->numeric()
                                            ->step(0.01)
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->suffix('%')
                                            ->placeholder('z.B. 5.00'),
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
