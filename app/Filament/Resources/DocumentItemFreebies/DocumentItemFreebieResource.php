<?php

namespace App\Filament\Resources\DocumentItemFreebies;

use App\Filament\Resources\DocumentItemFreebies\Pages\CreateDocumentItemFreebie;
use App\Filament\Resources\DocumentItemFreebies\Pages\EditDocumentItemFreebie;
use App\Filament\Resources\DocumentItemFreebies\Pages\ListDocumentItemFreebies;
use App\Filament\Resources\DocumentItemFreebies\Tables\DocumentItemFreebiesTable;
use App\Models\DocumentItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentItemFreebieResource extends Resource
{
    protected static ?string $model = DocumentItem::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    public static function getNavigationLabel(): string
    {
        return 'Freebie Items';
    }

    public static function getModelLabel(): string
    {
        return 'Freebie Items';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Sales';
    }

    public static function getNavigationSort(): ?int
    {
        return 112;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'document:id,customer_id,document_number,issued_at',
                'document.customer:id,name,nick',
            ])
            ->where('is_freebie', 1);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return DocumentItemFreebiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentItemFreebies::route('/'),
            'create' => CreateDocumentItemFreebie::route('/create'),
            'edit' => EditDocumentItemFreebie::route('/{record}/edit'),
        ];
    }
}
