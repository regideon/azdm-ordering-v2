<?php

namespace App\Filament\Resources\DocumentWebsiteOrders;

use App\Filament\Resources\DocumentWebsiteOrders\Pages\ListDocumentWebsiteOrders;
use App\Filament\Resources\DocumentWebsiteOrders\Tables\DocumentWebsiteOrdersTable;
use App\Models\Document;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentWebsiteOrdersResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    public static function getNavigationGroup(): ?string
    {
        return 'Sales';
    }

    public static function getNavigationSort(): ?int
    {
        return 132;
    }

    public static function getModelLabel(): string
    {
        return 'Website Orders';
    }

    public static function getNavigationLabel(): string
    {
        return 'Website Orders';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select([
                'documents.id',
                'documents.document_number',
                'documents.customer_name',
                'documents.customer_nick',
                'documents.items_search',
                'documents.grand_amount',
                'documents.issued_at',
                'documents.payment_sort_id',
                'documents.document_status_id',
            ])
            ->where('payment_sort_id', 8)
            ->with([
                'paymentSort:id,name',
                'documentStatus:id,name',
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return DocumentWebsiteOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentWebsiteOrders::route('/'),
        ];
    }
}
