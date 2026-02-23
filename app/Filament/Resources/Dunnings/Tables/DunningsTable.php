<?php

namespace App\Filament\Resources\Dunnings\Tables;

use App\Enums\DunningLevel;
use App\Models\Dunning;
use App\Services\PdfService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DunningsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice.invoice_number')
                    ->label('Rechnungsnr.')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('invoice.customer.name')
                    ->label('Kunde')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('level')
                    ->label('Stufe')
                    ->badge(),
                TextColumn::make('dunning_date')
                    ->label('Mahndatum')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Frist bis')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('fee')
                    ->label('Gebühr')
                    ->money('EUR')
                    ->sortable(),
            ])
            ->defaultSort('dunning_date', 'desc')
            ->filters([
                SelectFilter::make('level')
                    ->label('Stufe')
                    ->options(DunningLevel::class),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('downloadPdf')
                    ->label('PDF')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('success')
                    ->action(function (Dunning $record) {
                        $service = app(PdfService::class);
                        $content = $service->generateDunning($record);
                        $filename = 'Mahnung_' . $record->invoice->invoice_number . '_' . $record->level->getLabel() . '.pdf';

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
