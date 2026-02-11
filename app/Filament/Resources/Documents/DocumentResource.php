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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    // protected static ?string $recordTitleAttribute = 'document_number';



    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()            
            ->with([
                'paymentSort:id,name',
                'documentStatus:id,name',
                'customer:id,name,nick',
                'bankStatementBanks:id,name',
            ]);

        /*    
        return parent::getEloquentQuery()
            ->where('document_type_id', 1)
            ->select([
                // Primary
                'id',

                // Core document fields
                'document_type_id',
                'document_status_id',
                'payment_sort_id',
                'customer_id',
                'document_number',
                'tracking_number',
                'order_number',

                // Customer snapshot fields
                'customer_name',
                'customer_nick',
                'customer_phone',
                'customer_address',

                // Search / computed
                'items_search',

                'notes',

                // Amounts
                'grand_subtotal',
                'grand_amount',

                // Dates
                'issued_at',
                'due_at',
                'created_at',
                'updated_at',
                'return_at',
                'refunded_at',

                // Soft deletes (CRITICAL)
                'deleted_at',
                
                'attachments',
                'attachments_qc',
                'attachments_packing',
                'attachments_clerk',
                'document_shipment_type_id',
                'notes_clerk',
                'ship_at',
                'attachments_delivery',
                'notes_delivery',
                'attachments_orderchecker',
                'notes_orderchecker',

                'created_name',
                'updated_name',

            ])
            ->with([
                'paymentSort:id,name',          // include fields you display
                'documentStatus:id,name',
                'customer:id,name,nick',
                'bankStatementBanks:id,name', // example; tailor it
            ]);
            */
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
