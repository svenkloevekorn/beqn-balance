<?php

namespace App\Filament\Resources\Quotes\Tables;

use App\Enums\QuoteStatus;
use App\Models\Quote;
use App\Services\PdfService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quote_number')
                    ->label('Angebotsnr.')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Kunde')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quote_date')
                    ->label('Datum')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('valid_until')
                    ->label('Gueltig bis')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->defaultSort('quote_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(QuoteStatus::class),
                SelectFilter::make('year')
                    ->label('Jahr')
                    ->options(fn () => Quote::selectRaw('YEAR(quote_date) as year')
                        ->distinct()
                        ->orderByDesc('year')
                        ->pluck('year', 'year')
                        ->mapWithKeys(fn ($year) => [$year => $year])
                        ->toArray()
                    )
                    ->query(fn ($query, array $data) => $query->when(
                        $data['value'],
                        fn ($q, $year) => $q->whereYear('quote_date', $year),
                    )),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('downloadPdf')
                    ->label(fn ($record) => $record->status === QuoteStatus::Draft ? 'Vorschau' : 'PDF')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color(fn ($record) => $record->status === QuoteStatus::Draft ? 'gray' : 'success')
                    ->action(function ($record) {
                        $service = app(PdfService::class);
                        $isDraft = $record->status === QuoteStatus::Draft;
                        $content = $service->generateQuote($record, $isDraft);
                        $filename = $record->quote_number . ($isDraft ? '_ENTWURF' : '') . '.pdf';

                        return response()->streamDownload(fn () => print($content), $filename, [
                            'Content-Type' => 'application/pdf',
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
