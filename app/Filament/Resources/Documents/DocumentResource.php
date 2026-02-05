<?php

namespace App\Filament\Resources\Documents;

use App\Filament\Resources\Documents\Pages\CreateDocument;
use App\Filament\Resources\Documents\Pages\EditDocument;
use App\Filament\Resources\Documents\Pages\ListDocuments;
use App\Filament\Resources\Documents\Pages\ViewDocument;
use App\Filament\Resources\Documents\Schemas\DocumentForm;
use App\Filament\Resources\Documents\Schemas\DocumentInfolist;
use App\Filament\Resources\Documents\Tables\DocumentsTable;
use App\Models\Document;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'document_number';



    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('document_type_id', 1)
            ->select([
                'id',
                'document_type_id',
                'document_status_id',
                'payment_sort_id',
                'customer_id',
                'document_number',
                'customer_name',
                'customer_nick',
                'items_search',
                'customer_phone',
                'customer_address',
                'grand_amount',
                'issued_at',
                'created_at',
            ])
            ->with([
                'paymentSort:id,name',          // include fields you display
                'documentStatus:id,name',
                'customer:id,name,nick',
                'bankStatementBanks:id,name', // example; tailor it
            ]);
    }



    public static function getNavigationGroup(): ?string { return 'Sales'; }
    
    public static function getNavigationLabel(): string { return 'Orders'; }

    public static function getModelLabel(): string { return 'Orders'; }

    public static function getNavigationSort(): ?int { return 100; }




    public static function form(Schema $schema): Schema
    {
        return DocumentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DocumentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentsTable::configure($table);
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
            'index' => ListDocuments::route('/'),
            'create' => CreateDocument::route('/create'),
            'view' => ViewDocument::route('/{record}'),
            'edit' => EditDocument::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
