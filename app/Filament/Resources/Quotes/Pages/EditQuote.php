<?php

namespace App\Filament\Resources\Quotes\Pages;

use App\Enums\QuoteStatus;
use App\Filament\Resources\Quotes\QuoteResource;
use App\Services\PdfService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditQuote extends EditRecord
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label(fn () => $this->record->status === QuoteStatus::Draft ? 'Vorschau PDF' : 'PDF herunterladen')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color(fn () => $this->record->status === QuoteStatus::Draft ? 'gray' : 'success')
                ->action(function () {
                    $service = app(PdfService::class);
                    $isDraft = $this->record->status === QuoteStatus::Draft;
                    $content = $service->generateQuote($this->record, $isDraft);
                    $filename = $this->record->quote_number . ($isDraft ? '_ENTWURF' : '') . '.pdf';

                    return response()->streamDownload(fn () => print($content), $filename, [
                        'Content-Type' => 'application/pdf',
                    ]);
                }),
            DeleteAction::make(),
        ];
    }
}
