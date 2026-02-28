<?php

namespace App\Filament\Resources\DeliveryNotes\Pages;

use App\Enums\DeliveryNoteStatus;
use App\Filament\Resources\DeliveryNotes\DeliveryNoteResource;
use App\Services\PdfService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use App\Filament\Concerns\RedirectsToListPage;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditDeliveryNote extends EditRecord
{
    use RedirectsToListPage;
    protected static string $resource = DeliveryNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label(fn () => $this->record->status === DeliveryNoteStatus::Draft ? 'Vorschau PDF' : 'PDF herunterladen')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color(fn () => $this->record->status === DeliveryNoteStatus::Draft ? 'gray' : 'success')
                ->action(function () {
                    $service = app(PdfService::class);
                    $isDraft = $this->record->status === DeliveryNoteStatus::Draft;
                    $content = $service->generateDeliveryNote($this->record, $isDraft);
                    $filename = $this->record->delivery_note_number . ($isDraft ? '_ENTWURF' : '') . '.pdf';

                    return response()->streamDownload(fn () => print($content), $filename, [
                        'Content-Type' => 'application/pdf',
                    ]);
                }),
            DeleteAction::make(),
        ];
    }
}
