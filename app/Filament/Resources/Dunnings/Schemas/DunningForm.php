<?php

namespace App\Filament\Resources\Dunnings\Schemas;

use App\Enums\DunningLevel;
use App\Enums\InvoiceStatus;
use App\Models\CompanySetting;
use App\Models\Invoice;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DunningForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Mahnung')
                    ->columns(2)
                    ->schema([
                        Select::make('invoice_id')
                            ->label('Rechnung')
                            ->options(
                                Invoice::whereIn('status', [
                                    InvoiceStatus::Overdue,
                                    InvoiceStatus::Sent,
                                    InvoiceStatus::PartiallyPaid,
                                ])
                                    ->with('customer')
                                    ->get()
                                    ->mapWithKeys(fn (Invoice $inv) => [
                                        $inv->id => $inv->invoice_number . ' — ' . $inv->customer->name,
                                    ])
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if (! $state) {
                                    return;
                                }

                                $invoice = Invoice::with('dunnings')->find($state);
                                if (! $invoice) {
                                    return;
                                }

                                $lastDunning = $invoice->dunnings->last();
                                $nextLevel = $lastDunning
                                    ? ($lastDunning->level->next() ?? $lastDunning->level)
                                    : DunningLevel::Reminder;

                                $set('level', $nextLevel->value);

                                self::applyDefaults($set, $nextLevel);
                            })
                            ->live()
                            ->columnSpan(2),
                        Select::make('level')
                            ->label('Mahnstufe')
                            ->options(DunningLevel::class)
                            ->required()
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if (! $state) {
                                    return;
                                }
                                self::applyDefaults($set, DunningLevel::from($state));
                            })
                            ->live(),
                        DatePicker::make('dunning_date')
                            ->label('Mahndatum')
                            ->default(now())
                            ->required(),
                        DatePicker::make('due_date')
                            ->label('Zahlungsfrist bis')
                            ->default(now()->addDays(7))
                            ->required(),
                        TextInput::make('fee')
                            ->label('Mahngebuehr (€)')
                            ->numeric()
                            ->step(0.01)
                            ->default(0)
                            ->required(),
                    ]),
                Section::make('Inhalt')
                    ->schema([
                        TextInput::make('subject')
                            ->label('Betreff')
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('text')
                            ->label('Text')
                            ->rows(6)
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected static function applyDefaults(Set $set, DunningLevel $level): void
    {
        $settings = CompanySetting::instance();

        $prefix = match ($level) {
            DunningLevel::Reminder => 'dunning_reminder',
            DunningLevel::FirstWarning => 'dunning_first',
            DunningLevel::SecondWarning => 'dunning_second',
        };

        $set('subject', $settings->{$prefix . '_subject'} ?? $level->getLabel());
        $set('text', $settings->{$prefix . '_text'} ?? '');
        $set('fee', $settings->{$prefix . '_fee'} ?? 0);
        $set('due_date', now()->addDays($settings->{$prefix . '_days'} ?? 14)->format('Y-m-d'));
    }
}
