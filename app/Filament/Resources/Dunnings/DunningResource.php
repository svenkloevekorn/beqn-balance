<?php

namespace App\Filament\Resources\Dunnings;

use App\Filament\Resources\Dunnings\Pages\CreateDunning;
use App\Filament\Resources\Dunnings\Pages\EditDunning;
use App\Filament\Resources\Dunnings\Pages\ListDunnings;
use App\Filament\Resources\Dunnings\Schemas\DunningForm;
use App\Filament\Resources\Dunnings\Tables\DunningsTable;
use App\Models\Dunning;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DunningResource extends Resource
{
    protected static ?string $model = Dunning::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static string|\UnitEnum|null $navigationGroup = 'Rechnungswesen';

    protected static ?string $modelLabel = 'Mahnung';

    protected static ?string $pluralModelLabel = 'Mahnungen';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return DunningForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DunningsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDunnings::route('/'),
            'create' => CreateDunning::route('/create'),
            'edit' => EditDunning::route('/{record}/edit'),
        ];
    }
}
