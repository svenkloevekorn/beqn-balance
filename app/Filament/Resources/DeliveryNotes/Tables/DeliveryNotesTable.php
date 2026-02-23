<?php

namespace App\Filament\Resources\DeliveryNotes\Tables;

use App\Enums\DeliveryNoteStatus;
use App\Models\DeliveryNote;
use App\Services\PdfService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DeliveryNotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('delivery_note_number')
                    ->label('Lieferscheinnr.')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Kunde')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('delivery_date')
                    ->label('Lieferdatum')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->defaultSort('delivery_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(DeliveryNoteStatus::class),
                SelectFilter::make('year')
                    ->label('Jahr')
                    ->options(fn () => DeliveryNote::selectRaw('YEAR(delivery_date) as year')
                        ->distinct()
                        ->orderByDesc('year')
                        ->pluck('year', 'year')
                        ->mapWithKeys(fn ($year) => [$year => $year])
                        ->toArray()
                    )
                    ->query(fn ($query, array $data) => $query->when(
                        $data['value'],
                        fn ($q, $year) => $q->whereYear('delivery_date', $year),
                    )),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('downloadPdf')
                    ->label(fn ($record) => $record->status === DeliveryNoteStatus::Draft ? 'Vorschau' : 'PDF')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color(fn ($record) => $record->status === DeliveryNoteStatus::Draft ? 'gray' : 'success')
                    ->action(function ($record) {
                        $service = app(PdfService::class);
                        $isDraft = $record->status === DeliveryNoteStatus::Draft;
                        $content = $service->generateDeliveryNote($record, $isDraft);
                        $filename = $record->delivery_note_number . ($isDraft ? '_ENTWURF' : '') . '.pdf';

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
