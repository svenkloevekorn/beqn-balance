<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Models\Role;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rolle')
                    ->schema([
                        TextInput::make('name')
                            ->label('Rollenname')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ]),
                Section::make('Berechtigungen')
                    ->description('Lege fest, was Benutzer mit dieser Rolle duerfen.')
                    ->schema(static::buildPermissionFields())
                    ->columns(1),
            ]);
    }

    protected static function buildPermissionFields(): array
    {
        $fields = [];

        foreach (Role::$resources as $resourceKey => $resourceLabel) {
            $fields[] = Section::make($resourceLabel)
                ->schema(
                    collect(Role::$abilities)->map(function (string $abilityLabel, string $abilityKey) use ($resourceKey) {
                        return Checkbox::make("permissions.{$resourceKey}.{$abilityKey}")
                            ->label($abilityLabel)
                            ->inline();
                    })->values()->all()
                )
                ->columns(4)
                ->compact()
                ->collapsed();
        }

        return $fields;
    }
}
