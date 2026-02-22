<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\Article;
use App\Models\Customer;
use App\Models\Invoice;
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

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rechnungsdaten')
                    ->columns(2)
                    ->schema([
                        TextInput::make('invoice_number')
                            ->label('Rechnungsnummer')
                            ->default(fn () => NumberRange::where('type', 'invoice')->first()?->previewNext() ?? 'RE-0001')
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
                                if ($customer && $customer->payment_term_days) {
                                    $set('due_date', now()->addDays($customer->payment_term_days)->format('Y-m-d'));
                                }
                            })
                            ->live(),
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
                                'draft' => 'Entwurf',
                                'sent' => 'Versendet',
                                'paid' => 'Bezahlt',
                                'cancelled' => 'Storniert',
                            ])
                            ->default('draft')
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
                            ->addActionLabel('Position hinzufügen')
                            ->columnSpanFull(),
                    ]),
                Section::make('Bemerkungen')
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('notes')
                            ->label('')
                            ->rows(3)
                            ->placeholder('Bemerkungen zur Rechnung...')
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }
}
