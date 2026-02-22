<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                        TextInput::make('payment_term_days')
                            ->label('Zahlungsziel (Tage)')
                            ->numeric()
                            ->required()
                            ->default(14),
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
            ]);
    }
}
