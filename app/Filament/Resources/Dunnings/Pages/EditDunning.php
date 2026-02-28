<?php

namespace App\Filament\Resources\Dunnings\Pages;

use App\Filament\Resources\Dunnings\DunningResource;
use App\Models\Dunning;
use App\Services\PdfService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use App\Filament\Concerns\RedirectsToListPage;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditDunning extends EditRecord
{
    use RedirectsToListPage;
    protected static string $resource = DunningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('PDF herunterladen')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('success')
                ->action(function () {
                    $service = app(PdfService::class);
                    $content = $service->generateDunning($this->record);
                    $filename = 'Mahnung_' . $this->record->invoice->invoice_number . '_' . $this->record->level->getLabel() . '.pdf';

                    return response()->streamDownload(fn () => print($content), $filename, [
                        'Content-Type' => 'application/pdf',
                    ]);
                }),
            DeleteAction::make(),
        ];
    }
}
