<?php

namespace App\Filament\Resources\IncomingInvoices;

use App\Filament\Resources\IncomingInvoices\Pages\CreateIncomingInvoice;
use App\Filament\Resources\IncomingInvoices\Pages\EditIncomingInvoice;
use App\Filament\Resources\IncomingInvoices\Pages\ListIncomingInvoices;
use App\Filament\Resources\IncomingInvoices\Schemas\IncomingInvoiceForm;
use App\Filament\Resources\IncomingInvoices\Tables\IncomingInvoicesTable;
use App\Models\IncomingInvoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IncomingInvoiceResource extends Resource
{
    protected static ?string $model = IncomingInvoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowDown;

    protected static string|\UnitEnum|null $navigationGroup = 'Rechnungswesen';

    protected static ?string $modelLabel = 'Eingangsrechnung';

    protected static ?string $pluralModelLabel = 'Eingangsrechnungen';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return IncomingInvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IncomingInvoicesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIncomingInvoices::route('/'),
            'create' => CreateIncomingInvoice::route('/create'),
            'edit' => EditIncomingInvoice::route('/{record}/edit'),
        ];
    }
}
