<?php

namespace App\Filament\Resources\DocumentItems;

use App\Filament\Resources\DocumentItems\Pages\CreateDocumentItem;
use App\Filament\Resources\DocumentItems\Pages\EditDocumentItem;
use App\Filament\Resources\DocumentItems\Pages\ListDocumentItems;
use App\Filament\Resources\DocumentItems\Schemas\DocumentItemForm;
use App\Filament\Resources\DocumentItems\Schemas\DocumentItemInfolist;
use App\Filament\Resources\DocumentItems\Tables\DocumentItemsTable;
use App\Models\DocumentItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentItemResource extends Resource
{
    protected static ?string $model = DocumentItem::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

  

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'document:id,customer_id,document_status_id,document_number,issued_at',
                'document.documentStatus:id,name',
                'document.customer:id,name,nick',
            ])
            ->where('is_partial_delivery', 1);
    }

    public static function getNavigationGroup(): ?string { return 'Sales'; }
    
    public static function getNavigationLabel(): string { return 'Out of Stock Items'; }

    public static function getModelLabel(): string { return 'Out of Stock Items'; }

    public static function getNavigationSort(): ?int { return 104; }




    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return DocumentItemForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DocumentItemInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentItems::route('/'),
            'create' => CreateDocumentItem::route('/create'),
            'edit' => EditDocumentItem::route('/{record}/edit'),
        ];
    }
}
