<?php

namespace App\Filament\Pages;

use App\Models\CompanySetting;
use App\Models\NumberRange;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
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
            ]);
    }

    // --- Nummernkreise-Formular ---

    public function numberRangesForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('numberRangesData')
            ->components([
                Repeater::make('ranges')
                    ->label('')
                    ->schema([
                        TextInput::make('label')
                            ->label('Bezeichnung')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(2),
                        TextInput::make('prefix')
                            ->label('Praefix')
                            ->required()
                            ->maxLength(10)
                            ->columnSpan(1),
                        Checkbox::make('include_year')
                            ->label('Jahr einbeziehen')
                            ->columnSpan(1),
                        TextInput::make('next_number')
                            ->label('Naechste Nummer')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->columnSpan(1),
                        TextInput::make('digits')
                            ->label('Stellen (0 = keine Nullen)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(10)
                            ->columnSpan(1),
                        Checkbox::make('reset_yearly')
                            ->label('Jaehrlich zuruecksetzen')
                            ->columnSpan(1),
                        Placeholder::make('preview')
                            ->label('Vorschau')
                            ->content(function ($get): string {
                                $prefix = $get('prefix') ?? '??';
                                $includeYear = $get('include_year');
                                $number = (int) ($get('next_number') ?? 1);
                                $digits = (int) ($get('digits') ?? 0);

                                $formatted = $digits > 0
                                    ? str_pad($number, $digits, '0', STR_PAD_LEFT)
                                    : (string) $number;

                                if ($includeYear) {
                                    return "{$prefix}-" . now()->year . "-{$formatted}";
                                }

                                return "{$prefix}-{$formatted}";
                            })
                            ->columnSpan(1),
                        TextInput::make('id')->hidden()->dehydrated(),
                        TextInput::make('type')->hidden()->dehydrated(),
                    ])
                    ->columns(8)
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->live()
                    ->columnSpanFull(),
            ]);
    }

    // --- Content mit Tabs ---

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Einstellungen')
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
                'prefix' => $rangeData['prefix'],
                'include_year' => $rangeData['include_year'] ?? false,
                'next_number' => $rangeData['next_number'],
                'digits' => $rangeData['digits'],
                'reset_yearly' => $rangeData['reset_yearly'] ?? false,
            ]);
        }

        Notification::make()
            ->success()
            ->title('Nummernkreise gespeichert')
            ->send();
    }
}
