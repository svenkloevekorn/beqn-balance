<?php

namespace App\Filament\Pages;

use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class ActivityLogPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'Einstellungen';

    protected static ?string $navigationLabel = 'Aktivitaetslog';

    protected static ?string $title = 'Aktivitaetslog';

    protected static ?int $navigationSort = 101;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && $user->hasPermission('settings', 'view');
    }

    protected string $view = 'filament-panels::pages.page';

    public function table(Table $table): Table
    {
        return $table
            ->query(Activity::query()->with('causer'))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Zeitpunkt')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
                TextColumn::make('causer.name')
                    ->label('Benutzer')
                    ->default('System'),
                TextColumn::make('log_name')
                    ->label('Bereich')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'auth' => 'Anmeldung',
                        default => 'Daten',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'auth' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('event')
                    ->label('Aktion')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'created' => 'Erstellt',
                        'updated' => 'Bearbeitet',
                        'deleted' => 'Geloescht',
                        default => $state ?? '-',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('subject_type')
                    ->label('Typ')
                    ->formatStateUsing(fn (?string $state) => $state ? self::modelLabel($state) : '-'),
                TextColumn::make('description')
                    ->label('Beschreibung')
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('causer_id')
                    ->label('Benutzer')
                    ->options(fn () => User::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        if (! $data['value']) {
                            return $query;
                        }

                        return $query
                            ->where('causer_type', User::class)
                            ->where('causer_id', $data['value']);
                    }),
                SelectFilter::make('event')
                    ->label('Aktion')
                    ->options([
                        'created' => 'Erstellt',
                        'updated' => 'Bearbeitet',
                        'deleted' => 'Geloescht',
                    ]),
                SelectFilter::make('subject_type')
                    ->label('Typ')
                    ->options(fn () => Activity::query()
                        ->distinct()
                        ->whereNotNull('subject_type')
                        ->pluck('subject_type')
                        ->mapWithKeys(fn (string $class) => [$class => self::modelLabel($class)])
                        ->sort()
                        ->toArray()
                    ),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('von')
                            ->label('Von'),
                        DatePicker::make('bis')
                            ->label('Bis'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['von'], fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['bis'], fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([])
            ->paginated([25, 50, 100]);
    }

    protected static function modelLabel(string $class): string
    {
        return match ($class) {
            'App\\Models\\Customer' => 'Kunde',
            'App\\Models\\Supplier' => 'Lieferant',
            'App\\Models\\ContactPerson' => 'Ansprechpartner',
            'App\\Models\\Article' => 'Artikel',
            'App\\Models\\Category' => 'Kategorie',
            'App\\Models\\Invoice' => 'Rechnung',
            'App\\Models\\InvoiceItem' => 'Rechnungsposition',
            'App\\Models\\IncomingInvoice' => 'Eingangsrechnung',
            'App\\Models\\Quote' => 'Angebot',
            'App\\Models\\QuoteItem' => 'Angebotsposition',
            'App\\Models\\DeliveryNote' => 'Lieferschein',
            'App\\Models\\DeliveryNoteItem' => 'Lieferscheinposition',
            'App\\Models\\Payment' => 'Zahlung',
            'App\\Models\\Dunning' => 'Mahnung',
            'App\\Models\\User' => 'Benutzer',
            'App\\Models\\Role' => 'Rolle',
            'App\\Models\\CompanySetting' => 'Einstellungen',
            default => class_basename($class),
        };
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            $this->table($this->makeTable()),
        ]);
    }
}
