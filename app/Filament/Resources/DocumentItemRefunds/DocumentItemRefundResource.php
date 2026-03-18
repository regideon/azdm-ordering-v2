<?php

namespace App\Filament\Resources\DocumentItemRefunds;

use App\Filament\Resources\DocumentItemRefunds\Pages\CreateDocumentItemRefund;
use App\Filament\Resources\DocumentItemRefunds\Pages\EditDocumentItemRefund;
use App\Filament\Resources\DocumentItemRefunds\Pages\ListDocumentItemRefunds;
use App\Filament\Resources\DocumentItemRefunds\Tables\DocumentItemRefundsTable;
use App\Models\DocumentItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentItemRefundResource extends Resource
{
    protected static ?string $model = DocumentItem::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    public static function getNavigationLabel(): string
    {
        return 'Refund Items';
    }

    public static function getModelLabel(): string
    {
        return 'Refund Items';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Sales';
    }

    public static function getNavigationSort(): ?int
    {
        return 120;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'document:id,customer_id,document_number,issued_at',
                'document.customer:id,name,nick',
            ])
            ->where('is_refunds', 1);
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
        return DocumentItemRefundsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentItemRefunds::route('/'),
            'create' => CreateDocumentItemRefund::route('/create'),
            'edit' => EditDocumentItemRefund::route('/{record}/edit'),
        ];
    }
}
