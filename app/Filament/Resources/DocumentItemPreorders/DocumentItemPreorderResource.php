<?php

namespace App\Filament\Resources\DocumentItemPreorders;

use App\Filament\Resources\DocumentItemPreorders\Pages\CreateDocumentItemPreorder;
use App\Filament\Resources\DocumentItemPreorders\Pages\EditDocumentItemPreorder;
use App\Filament\Resources\DocumentItemPreorders\Pages\ListDocumentItemPreorders;
use App\Filament\Resources\DocumentItemPreorders\Tables\DocumentItemPreordersTable;
use App\Models\DocumentItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentItemPreorderResource extends Resource
{
    protected static ?string $model = DocumentItem::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    public static function getNavigationLabel(): string
    {
        return 'Preorder Items';
    }

    public static function getModelLabel(): string
    {
        return 'Preorder Items';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Sales';
    }

    public static function getNavigationSort(): ?int
    {
        return 108;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'document:id,customer_id,document_status_id,document_number,issued_at',
                'document.documentStatus:id,name',
                'document.customer:id,name,nick',
            ])
            ->where('is_preorder', 1);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return DocumentItemPreordersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentItemPreorders::route('/'),
            'create' => CreateDocumentItemPreorder::route('/create'),
            'edit' => EditDocumentItemPreorder::route('/{record}/edit'),
        ];
    }
}
