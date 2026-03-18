<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    Hidden::make('guard_name')
                        ->default(config('auth.defaults.guard', 'web'))
                        ->dehydrated(),

                    TextInput::make('name')
                        ->minLength(2)
                        ->maxLength(190)
                        ->required()
                        ->unique(ignoreRecord: true),
                ]),

            Section::make('Permissions')
                ->description(fn (?Role $record): string => $record?->name ? "Set permissions for the '{$record->name}' role." : '')
                ->schema([
                    Select::make('permissions')
                        ->multiple()
                        ->relationship('permissions', 'name')
                        ->preload(),
                ]),
        ]);
    }
}
