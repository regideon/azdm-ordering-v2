<?php

namespace App\Filament\Resources\DocumentItemReturns;

use App\Filament\Resources\DocumentItemReturns\Pages\CreateDocumentItemReturn;
use App\Filament\Resources\DocumentItemReturns\Pages\EditDocumentItemReturn;
use App\Filament\Resources\DocumentItemReturns\Pages\ListDocumentItemReturns;
use App\Filament\Resources\DocumentItemReturns\Tables\DocumentItemReturnsTable;
use App\Models\DocumentItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentItemReturnResource extends Resource
{
    protected static ?string $model = DocumentItem::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    public static function getNavigationLabel(): string
    {
        return 'Return Items';
    }

    public static function getModelLabel(): string
    {
        return 'Return Items';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Sales';
    }

    public static function getNavigationSort(): ?int
    {
        return 116;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'document:id,customer_id,document_number,issued_at',
                'document.customer:id,name,nick',
            ])
            ->where('is_returns', 1);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return DocumentItemReturnsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentItemReturns::route('/'),
            'create' => CreateDocumentItemReturn::route('/create'),
            'edit' => EditDocumentItemReturn::route('/{record}/edit'),
        ];
    }
}
