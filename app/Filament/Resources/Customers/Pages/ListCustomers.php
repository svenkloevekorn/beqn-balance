<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Imports\CustomersImport;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getDownloadTemplateAction(),
            $this->getImportAction(),
            CreateAction::make(),
        ];
    }

    protected function getDownloadTemplateAction(): Action
    {
        return Action::make('downloadTemplate')
            ->label('Vorlage')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->action(function () {
                return response()->download(
                    resource_path('templates/kunden_import_vorlage.csv'),
                    'kunden_import_vorlage.csv'
                );
            });
    }

    protected function getImportAction(): Action
    {
        return Action::make('import')
            ->label('Importieren')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('gray')
            ->form([
                FileUpload::make('file')
                    ->label('CSV- oder Excel-Datei')
                    ->acceptedFileTypes([
                        'text/csv',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ])
                    ->required()
                    ->storeFiles(false),
            ])
            ->action(function (array $data) {
                $file = $data['file'];

                $import = new CustomersImport();

                try {
                    Excel::import($import, $file);
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Import fehlgeschlagen')
                        ->body('Fehler: ' . $e->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                $parts = [];
                if ($import->imported > 0) {
                    $parts[] = $import->imported . ' neu angelegt';
                }
                if ($import->updated > 0) {
                    $parts[] = $import->updated . ' aktualisiert';
                }
                if ($import->skipped > 0) {
                    $parts[] = $import->skipped . ' übersprungen';
                }

                Notification::make()
                    ->title('Import abgeschlossen')
                    ->body(implode(', ', $parts) ?: 'Keine Daten importiert.')
                    ->success()
                    ->send();
            })
            ->modalHeading('Kunden importieren')
            ->modalDescription('Lade eine CSV- oder Excel-Datei mit Kundendaten hoch. Bestehende Kunden (gleicher Name) werden aktualisiert.')
            ->modalSubmitActionLabel('Importieren');
    }
}
