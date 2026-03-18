<?php

namespace App\Filament\Resources\Permissions\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Hidden::make('guard_name')
                ->default(config('auth.defaults.guard', 'web'))
                ->dehydrated(),

            TextInput::make('name')
                ->minLength(2)
                ->maxLength(190)
                ->required()
                ->unique(ignoreRecord: true),
        ]);
    }
}
