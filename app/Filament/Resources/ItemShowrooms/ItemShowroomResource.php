<?php

namespace App\Filament\Resources\ItemShowrooms;

use App\Filament\Resources\ItemShowrooms\Pages\ListItemShowrooms;
use App\Filament\Resources\ItemShowrooms\Tables\ItemShowroomsTable;
use App\Models\Item;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ItemShowroomResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    public static function getNavigationLabel(): string
    {
        return 'Showroom';
    }

    public static function getNavigationSort(): ?int
    {
        return 228;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Inventory';
    }

    public static function getModelLabel(): string
    {
        return 'Showroom';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_showroom', 1);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return ItemShowroomsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListItemShowrooms::route('/'),
        ];
    }
}
