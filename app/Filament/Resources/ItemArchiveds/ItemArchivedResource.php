<?php

namespace App\Filament\Resources\ItemArchiveds;

use App\Filament\Resources\ItemArchiveds\Pages\ListItemArchiveds;
use App\Filament\Resources\ItemArchiveds\Tables\ItemArchivedsTable;
use App\Models\Item;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ItemArchivedResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box-x-mark';

    public static function getNavigationLabel(): string
    {
        return 'Old Items';
    }

    public static function getNavigationSort(): ?int
    {
        return 252;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Reports';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_archived', 1);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return ItemArchivedsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListItemArchiveds::route('/'),
        ];
    }
}
