<?php

namespace App\Filament\Pages;

use App\Models\CompanySetting;
use App\Models\NumberRange;
use App\Services\NumberFormatService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CompanySettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'Einstellungen';

    protected static ?string $navigationLabel = 'Einstellungen';

    protected static ?string $title = 'Einstellungen';

    protected static ?int $navigationSort = 100;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && $user->hasPermission('settings', 'view');
    }

    protected string $view = 'filament-panels::pages.page';

    public ?array $companyData = [];

    public ?array $numberRangesData = [];

    public function mount(): void
    {
        $settings = CompanySetting::instance();
        $this->companyForm->fill($settings->toArray());

        $ranges = NumberRange::orderBy('id')->get()->toArray();
        $this->numberRangesForm->fill(['ranges' => $ranges]);
    }

    // --- Firmenstammdaten-Formular ---

    public function companyForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('companyData')
            ->components([
                Section::make('Unternehmen')
                    ->columns(2)
                    ->schema([
                        TextInput::make('company_name')
                            ->label('Firmenname')
                            ->maxLength(255)
                            ->columnSpan(2),
                        TextInput::make('legal_form')
                            ->label('Rechtsform')
                            ->placeholder('z.B. GmbH, UG, Einzelunternehmen')
                            ->maxLength(255),
                        TextInput::make('managing_director')
                            ->label('Geschaeftsfuehrer')
                            ->maxLength(255),
                    ]),

                Section::make('Adresse')
                    ->columns(2)
                    ->schema([
                        TextInput::make('street')
                            ->label('Strasse')
                            ->maxLength(255)
                            ->columnSpan(2),
                        TextInput::make('zip')
                            ->label('PLZ')
                            ->maxLength(10),
                        TextInput::make('city')
                            ->label('Ort')
                            ->maxLength(255),
                        Select::make('country')
                            ->label('Land')
                            ->options([
                                'DE' => 'Deutschland',
                                'AT' => 'Oesterreich',
                                'CH' => 'Schweiz',
                            ])
                            ->default('DE'),
                    ]),

                Section::make('Kontakt')
                    ->columns(2)
                    ->schema([
                        TextInput::make('phone')
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('fax')
                            ->label('Fax')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('E-Mail')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('website')
                            ->label('Website')
                            ->url()
                            ->maxLength(255),
                    ]),

                Section::make('Steuerdaten')
                    ->columns(2)
                    ->schema([
                        TextInput::make('vat_id')
                            ->label('USt-IdNr.')
                            ->placeholder('DE123456789')
                            ->maxLength(255),
                        TextInput::make('tax_number')
                            ->label('Steuernummer')
                            ->maxLength(255),
                        TextInput::make('trade_register')
                            ->label('Handelsregister')
                            ->placeholder('z.B. HRB 12345, AG Muenchen')
                            ->maxLength(255)
                            ->columnSpan(2),
                    ]),

                Section::make('Bankverbindung')
                    ->columns(3)
                    ->schema([
                        TextInput::make('bank_name')
                            ->label('Bank')
                            ->maxLength(255),
                        TextInput::make('iban')
                            ->label('IBAN')
                            ->maxLength(34),
                        TextInput::make('bic')
                            ->label('BIC')
                            ->maxLength(11),
                    ]),

                Section::make('Logo')
                    ->schema([
                        FileUpload::make('logo_path')
                            ->label('Firmenlogo')
                            ->image()
                            ->directory('company')
                            ->maxSize(2048)
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('3:1')
                            ->imageResizeTargetWidth('600')
                            ->imageResizeTargetHeight('200'),
                    ]),

                Section::make('Briefpapier')
                    ->description('PDF-Hintergrund fuer Rechnungen, Angebote und Lieferscheine')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('letterhead_path')
                            ->label('Briefpapier (PDF)')
                            ->acceptedFileTypes(['application/pdf'])
                            ->directory('company')
                            ->maxSize(5120)
                            ->columnSpan(2),
                        Checkbox::make('use_letterhead')
                            ->label('Briefpapier fuer PDF-Export verwenden')
                            ->columnSpan(2),
                    ]),
            ]);
    }

    // --- Nummernkreise-Formular ---

    public function numberRangesForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('numberRangesData')
            ->components([
                Section::make('Platzhalter-Referenz')
                    ->collapsed()
                    ->icon(Heroicon::OutlinedQuestionMarkCircle)
                    ->schema([
                        Placeholder::make('help')
                            ->label('')
                            ->content(new HtmlString(
                                self::buildPlaceholderReference()
                            )),
                    ]),
                Repeater::make('ranges')
                    ->label('')
                    ->schema([
                        TextInput::make('label')
                            ->label('Bezeichnung')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(2),
                        TextInput::make('format')
                            ->label('Format')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('z.B. RE-{jjjj}-{jz,4}')
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, $fail) {
                                        $service = app(NumberFormatService::class);
                                        $errors = $service->validate($value);
                                        foreach ($errors as $error) {
                                            $fail($error);
                                        }
                                    };
                                },
                            ])
                            ->columnSpan(3),
                        Placeholder::make('counters_display')
                            ->label('Zaehler')
                            ->content(function ($get): string {
                                $parts = [];
                                $cg = (int) ($get('counter_global') ?? 0);
                                $cy = (int) ($get('counter_yearly') ?? 0);
                                $cm = (int) ($get('counter_monthly') ?? 0);
                                $cd = (int) ($get('counter_daily') ?? 0);

                                if ($cg > 0) {
                                    $parts[] = "z={$cg}";
                                }
                                if ($cy > 0) {
                                    $parts[] = "jz={$cy}";
                                }
                                if ($cm > 0) {
                                    $parts[] = "mz={$cm}";
                                }
                                if ($cd > 0) {
                                    $parts[] = "tz={$cd}";
                                }

                                return $parts ? implode(' | ', $parts) : 'Alle auf 0';
                            })
                            ->columnSpan(2),
                        Placeholder::make('preview')
                            ->label('Naechste Nummer')
                            ->content(function ($get): string {
                                $format = $get('format') ?? '';
                                if (! $format) {
                                    return '-';
                                }

                                $service = app(NumberFormatService::class);
                                $errors = $service->validate($format);
                                if (! empty($errors)) {
                                    return 'Ungueltig';
                                }

                                // Simuliere Preview mit aktuellen Zaehlerstaenden
                                $counters = [
                                    'counter_global' => ((int) ($get('counter_global') ?? 0)) + 1,
                                    'counter_yearly' => ((int) ($get('counter_yearly') ?? 0)) + 1,
                                    'counter_monthly' => ((int) ($get('counter_monthly') ?? 0)) + 1,
                                    'counter_daily' => ((int) ($get('counter_daily') ?? 0)) + 1,
                                ];

                                return $service->generate($format, $counters);
                            })
                            ->columnSpan(2),
                        // Hidden fields
                        TextInput::make('id')->hidden()->dehydrated(),
                        TextInput::make('type')->hidden()->dehydrated(),
                        TextInput::make('counter_global')->hidden()->dehydrated(),
                        TextInput::make('counter_yearly')->hidden()->dehydrated(),
                        TextInput::make('counter_monthly')->hidden()->dehydrated(),
                        TextInput::make('counter_daily')->hidden()->dehydrated(),
                    ])
                    ->columns(9)
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->live()
                    ->columnSpanFull(),
            ]);
    }

    // --- Platzhalter-Hilfe ---

    protected static function buildPlaceholderReference(): string
    {
        $td = 'px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 border-b border-gray-100 dark:border-white/5';
        $code = 'font-mono text-sm font-semibold text-primary-600 dark:text-primary-400';
        $muted = 'text-gray-400 dark:text-gray-500';
        $stripe = 'bg-gray-50/60 dark:bg-white/[0.02]';

        $i = 0;
        $row = function (string $col1, string $col2, string $col3) use ($td, $code, $muted, $stripe, &$i) {
            $bg = ($i++ % 2 === 1) ? " style=\"background:rgba(0,0,0,0.02)\"" : '';

            return "<tr{$bg}>"
                . "<td style=\"padding:6px 12px;white-space:nowrap\"><code style=\"font-family:monospace;font-weight:600;color:var(--c-primary-600, #4f46e5)\">{$col1}</code></td>"
                . "<td style=\"padding:6px 12px\">{$col2}</td>"
                . "<td style=\"padding:6px 12px;color:#9ca3af;font-style:italic\">{$col3}</td>"
                . '</tr>';
        };

        $section = function (string $title, array $headers, array $rows) use (&$i) {
            $i = 0;
            $html = '<div style="margin-bottom:20px">'
                . "<p style=\"font-size:13px;font-weight:700;margin-bottom:6px;color:var(--c-primary-600, #4f46e5)\">{$title}</p>"
                . '<table style="width:100%;font-size:13px;border-collapse:collapse;border:1px solid rgba(0,0,0,0.1);border-radius:8px;overflow:hidden">'
                . '<thead><tr style="background:rgba(0,0,0,0.04)">';
            foreach ($headers as $h) {
                $html .= "<th style=\"padding:8px 12px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#6b7280;border-bottom:2px solid rgba(0,0,0,0.08)\">{$h}</th>";
            }
            $html .= '</tr></thead><tbody>';
            foreach ($rows as $r) {
                $html .= $r;
            }
            $html .= '</tbody></table></div>';

            return $html;
        };

        return '<div>'
            . $section('Datum-Platzhalter', ['Platzhalter', 'Beschreibung', 'Beispiel'], [
                $row('{jjjj}', 'Jahr, 4-stellig', '2026'),
                $row('{jj}', 'Jahr, 2-stellig', '26'),
                $row('{mm}', 'Monat mit fuehrender Null', '02'),
                $row('{m}', 'Monat ohne fuehrende Null', '2'),
                $row('{tt}', 'Tag mit fuehrender Null', '05'),
                $row('{t}', 'Tag ohne fuehrende Null', '5'),
                $row('{datum}', 'Kompaktdatum (JJJJMMTT)', '20260222'),
            ])
            . $section('Zaehler-Platzhalter', ['Platzhalter', 'Zuruecksetzung', 'Beschreibung'], [
                $row('{z}', 'Nie', 'Fortlaufend, zaehlt immer weiter'),
                $row('{jz}', 'Jaehrlich', 'Startet am 1. Januar bei 1'),
                $row('{mz}', 'Monatlich', 'Startet am 1. des Monats bei 1'),
                $row('{tz}', 'Taeglich', 'Startet jeden Tag bei 1'),
            ])
            . $section('Fuehrende Nullen', ['Schreibweise', 'Bedeutung', 'Ergebnis'], [
                $row('{z,4}', '4 Stellen Mindestbreite', '0001, 0042, 10000'),
                $row('{jz,5}', '5 Stellen Mindestbreite', '00001, 00042'),
            ])
            . $section('Format-Beispiele', ['Format', 'Einsatz', 'Ergebnis'], [
                $row('RE-{jjjj}-{jz,4}', 'Rechnung', 'RE-2026-0001'),
                $row('AN-{jj}{mm}{tt}{z,3}', 'Angebot mit Datum', 'AN-260222001'),
                $row('KD-{z,4}', 'Kundennummer', 'KD-0001'),
                $row('LS-{jjjj}-{jz,4}', 'Lieferschein', 'LS-2026-0001'),
            ])
            . '</div>';
    }

    // --- Content mit Tabs ---

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Einstellungen')
                    ->contained(false)
                    ->persistTabInQueryString()
                    ->schema([
                        Tab::make('Firmenstammdaten')
                            ->icon(Heroicon::OutlinedBuildingOffice)
                            ->schema([
                                Form::make([
                                    EmbeddedSchema::make('companyForm'),
                                ])
                                    ->id('company-form')
                                    ->livewireSubmitHandler('saveCompany')
                                    ->footer([
                                        Actions::make([
                                            $this->getSaveCompanyAction(),
                                        ]),
                                    ]),
                            ]),

                        Tab::make('Nummernkreise')
                            ->icon(Heroicon::OutlinedHashtag)
                            ->schema([
                                Form::make([
                                    EmbeddedSchema::make('numberRangesForm'),
                                ])
                                    ->id('number-ranges-form')
                                    ->livewireSubmitHandler('saveNumberRanges')
                                    ->footer([
                                        Actions::make([
                                            $this->getSaveNumberRangesAction(),
                                        ]),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    // --- Aktionen ---

    protected function getSaveCompanyAction(): Action
    {
        return Action::make('saveCompany')
            ->label('Firmenstammdaten speichern')
            ->submit('company-form')
            ->keyBindings(['mod+s']);
    }

    protected function getSaveNumberRangesAction(): Action
    {
        return Action::make('saveNumberRanges')
            ->label('Nummernkreise speichern')
            ->submit('number-ranges-form');
    }

    // --- Speichern ---

    public function saveCompany(): void
    {
        $data = $this->companyForm->getState();

        $settings = CompanySetting::instance();
        $settings->update($data);

        Notification::make()
            ->success()
            ->title('Firmenstammdaten gespeichert')
            ->send();
    }

    public function saveNumberRanges(): void
    {
        $data = $this->numberRangesForm->getState();

        foreach ($data['ranges'] as $rangeData) {
            NumberRange::where('id', $rangeData['id'])->update([
                'format' => $rangeData['format'],
            ]);
        }

        Notification::make()
            ->success()
            ->title('Nummernkreise gespeichert')
            ->send();
    }
}
