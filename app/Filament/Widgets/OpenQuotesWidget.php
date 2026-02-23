<?php

namespace App\Filament\Widgets;

use App\Enums\QuoteStatus;
use App\Filament\Resources\Quotes\QuoteResource;
use App\Models\Quote;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class OpenQuotesWidget extends TableWidget
{
    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'Offene Angebote';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Quote::query()
                    ->whereIn('status', [QuoteStatus::Draft, QuoteStatus::Sent])
                    ->latest('quote_date')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('quote_number')
                    ->label('Nr.'),
                TextColumn::make('customer.name')
                    ->label('Kunde'),
                TextColumn::make('valid_until')
                    ->label('Gültig bis')
                    ->date('d.m.Y'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->paginated(false)
            ->recordUrl(fn (Quote $record) => QuoteResource::getUrl('edit', ['record' => $record]));
    }
}
