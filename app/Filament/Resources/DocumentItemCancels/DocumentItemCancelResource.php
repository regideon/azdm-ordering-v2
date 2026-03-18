<?php

namespace App\Filament\Resources\DocumentItemCancels;

use App\Filament\Resources\DocumentItemCancels\Pages\CreateDocumentItemCancel;
use App\Filament\Resources\DocumentItemCancels\Pages\EditDocumentItemCancel;
use App\Filament\Resources\DocumentItemCancels\Pages\ListDocumentItemCancels;
use App\Filament\Resources\DocumentItemCancels\Tables\DocumentItemCancelsTable;
use App\Models\DocumentItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentItemCancelResource extends Resource
{
    protected static ?string $model = DocumentItem::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    public static function getNavigationLabel(): string
    {
        return 'Cancelled Items';
    }

    public static function getModelLabel(): string
    {
        return 'Cancelled Items';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Sales';
    }

    public static function getNavigationSort(): ?int
    {
        return 128;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'document:id,customer_id,document_number,issued_at',
                'document.customer:id,name,nick',
            ])
            ->where('is_cancelled', 1);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return DocumentItemCancelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentItemCancels::route('/'),
            'create' => CreateDocumentItemCancel::route('/create'),
            'edit' => EditDocumentItemCancel::route('/{record}/edit'),
        ];
    }
}
