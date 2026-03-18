<?php

namespace App\Filament\Resources\ItemHistories;

use App\Filament\Resources\ItemHistories\Pages\ListItemHistories;
use App\Filament\Resources\ItemHistories\Schemas\ItemHistoryForm;
use App\Filament\Resources\ItemHistories\Tables\ItemHistoriesTable;
use App\Models\ItemHistory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ItemHistoryResource extends Resource
{
    protected static ?string $model = ItemHistory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-battery-100';

    public static function getNavigationLabel(): string
    {
        return 'History';
    }

    public static function getNavigationSort(): ?int
    {
        return 230;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Inventory';
    }

    public static function getModelLabel(): string
    {
        return 'History';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderByDesc('id');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ItemHistoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ItemHistoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListItemHistories::route('/'),
        ];
    }
}
