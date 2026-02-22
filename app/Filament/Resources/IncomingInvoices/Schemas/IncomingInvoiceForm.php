<?php

namespace App\Filament\Resources\IncomingInvoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IncomingInvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rechnungsdaten')
                    ->columns(2)
                    ->schema([
                        Select::make('supplier_id')
                            ->label('Lieferant')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('external_invoice_number')
                            ->label('Externe Rechnungsnr.')
                            ->maxLength(255),
                        DatePicker::make('invoice_date')
                            ->label('Rechnungsdatum')
                            ->default(now())
                            ->required(),
                        DatePicker::make('due_date')
                            ->label('Fälligkeitsdatum')
                            ->default(now()->addDays(14))
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'open' => 'Offen',
                                'paid' => 'Bezahlt',
                                'cancelled' => 'Storniert',
                            ])
                            ->default('open')
                            ->required(),
                    ]),
                Section::make('Beträge')
                    ->columns(3)
                    ->schema([
                        TextInput::make('net_amount')
                            ->label('Nettobetrag (€)')
                            ->numeric()
                            ->step(0.01)
                            ->default(0)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $net = (float) $get('net_amount');
                                $vat = (float) $get('vat_amount');
                                $set('gross_amount', number_format($net + $vat, 2, '.', ''));
                            }),
                        TextInput::make('vat_amount')
                            ->label('MwSt-Betrag (€)')
                            ->numeric()
                            ->step(0.01)
                            ->default(0)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $net = (float) $get('net_amount');
                                $vat = (float) $get('vat_amount');
                                $set('gross_amount', number_format($net + $vat, 2, '.', ''));
                            }),
                        TextInput::make('gross_amount')
                            ->label('Bruttobetrag (€)')
                            ->numeric()
                            ->step(0.01)
                            ->default(0)
                            ->required(),
                    ]),
                Section::make('Bemerkungen')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Bemerkungen')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
