<?php

namespace App\Filament\Resources\DocumentItemOffsets;

use App\Filament\Resources\DocumentItemOffsets\Pages\CreateDocumentItemOffset;
use App\Filament\Resources\DocumentItemOffsets\Pages\EditDocumentItemOffset;
use App\Filament\Resources\DocumentItemOffsets\Pages\ListDocumentItemOffsets;
use App\Filament\Resources\DocumentItemOffsets\Tables\DocumentItemOffsetsTable;
use App\Models\DocumentItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentItemOffsetResource extends Resource
{
    protected static ?string $model = DocumentItem::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    public static function getNavigationLabel(): string
    {
        return 'Offset Items';
    }

    public static function getModelLabel(): string
    {
        return 'Offset Items';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Sales';
    }

    public static function getNavigationSort(): ?int
    {
        return 124;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'document:id,customer_id,document_number,issued_at',
                'document.customer:id,name,nick',
            ])
            ->where('is_offset', 1);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return DocumentItemOffsetsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentItemOffsets::route('/'),
            'create' => CreateDocumentItemOffset::route('/create'),
            'edit' => EditDocumentItemOffset::route('/{record}/edit'),
        ];
    }
}
