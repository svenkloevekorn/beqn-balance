<?php

namespace App\Filament\Pages;

use App\Imports\CustomersImport;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;

class ImportExport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|\UnitEnum|null $navigationGroup = 'Einstellungen';

    protected static ?string $navigationLabel = 'Import / Export';

    protected static ?string $title = 'Import / Export';

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament-panels::pages.page';

    public function getHeaderActions(): array
    {
        return [
            $this->getDownloadCustomerTemplateAction(),
        ];
    }

    protected function getDownloadCustomerTemplateAction(): Action
    {
        return Action::make('downloadCustomerTemplate')
            ->label('Kunden-Vorlage herunterladen')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->action(function () {
                return response()->download(
                    resource_path('templates/kunden_import_vorlage.csv'),
                    'kunden_import_vorlage.csv'
                );
            });
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Import / Export')
                    ->contained(false)
                    ->persistTabInQueryString()
                    ->schema([
                        Tab::make('Import')
                            ->icon(Heroicon::OutlinedArrowUpTray)
                            ->schema([
                                Section::make('Kunden importieren')
                                    ->description('CSV- oder Excel-Datei hochladen. Bestehende Kunden (gleicher Name) werden aktualisiert.')
                                    ->icon(Heroicon::OutlinedUsers)
                                    ->schema([
                                        \Filament\Schemas\Components\Actions::make([
                                            Action::make('importCustomers')
                                                ->label('Kunden importieren')
                                                ->icon('heroicon-o-arrow-up-tray')
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
                                                    $import = new CustomersImport();

                                                    try {
                                                        Excel::import($import, $data['file']);
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
                                                        ->title('Kunden-Import abgeschlossen')
                                                        ->body(implode(', ', $parts) ?: 'Keine Daten importiert.')
                                                        ->success()
                                                        ->send();
                                                })
                                                ->modalHeading('Kunden importieren')
                                                ->modalDescription('Lade eine CSV- oder Excel-Datei mit Kundendaten hoch.')
                                                ->modalSubmitActionLabel('Importieren'),
                                        ]),
                                    ]),
                            ]),

                        Tab::make('Export')
                            ->icon(Heroicon::OutlinedArrowDownTray)
                            ->schema([
                                Section::make('Demnächst verfügbar')
                                    ->description('Export-Funktionen werden hier Schritt für Schritt ergänzt.')
                                    ->icon(Heroicon::OutlinedWrench)
                                    ->schema([]),
                            ]),
                    ]),
            ]);
    }
}
