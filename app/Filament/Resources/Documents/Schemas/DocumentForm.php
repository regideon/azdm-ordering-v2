<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('document_type_id')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('document_number'),
                TextInput::make('order_number'),
                TextInput::make('tracking_number'),
                Textarea::make('items_search')
                    ->columnSpanFull(),
                TextInput::make('status_deprecated'),
                Select::make('document_status_id')
                    ->relationship('documentStatus', 'name')
                    ->required()
                    ->default(4),
                DateTimePicker::make('issued_at')
                    ->required(),
                DateTimePicker::make('due_at')
                    ->required(),
                TextInput::make('grand_subtotal')
                    ->numeric(),
                TextInput::make('discount_type')
                    ->numeric(),
                TextInput::make('discount_value')
                    ->numeric(),
                Select::make('tax_id')
                    ->relationship('tax', 'name'),
                TextInput::make('tax_value')
                    ->numeric(),
                TextInput::make('grand_amount')
                    ->numeric(),
                TextInput::make('currency_code')
                    ->default('PHP'),
                TextInput::make('currency_rate')
                    ->numeric()
                    ->default(1.0),
                TextInput::make('document_category_id')
                    ->numeric()
                    ->default(1),
                Select::make('customer_id')
                    ->relationship('customer', 'name'),
                TextInput::make('customer_name'),
                TextInput::make('customer_nick'),
                TextInput::make('customer_email')
                    ->email(),
                TextInput::make('customer_tax_number'),
                TextInput::make('customer_phone')
                    ->tel(),
                Textarea::make('customer_address')
                    ->columnSpanFull(),
                TextInput::make('customer_city'),
                TextInput::make('customer_zip_code'),
                TextInput::make('customer_state'),
                TextInput::make('customer_country'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                Textarea::make('payment_info')
                    ->columnSpanFull(),
                Textarea::make('attachments_spaces_s3')
                    ->columnSpanFull(),
                Textarea::make('attachments_qc_spaces_s3')
                    ->columnSpanFull(),
                Textarea::make('attachments_packing_spaces_s3')
                    ->columnSpanFull(),
                Textarea::make('attachments_clerk_spaces_s3')
                    ->columnSpanFull(),
                Textarea::make('attachments_delivery_spaces_s3')
                    ->columnSpanFull(),
                Textarea::make('attachments_orderchecker_spaces_s3')
                    ->columnSpanFull(),
                Textarea::make('attachments')
                    ->columnSpanFull(),
                Textarea::make('attachments_qc')
                    ->columnSpanFull(),
                Textarea::make('attachments_packing')
                    ->columnSpanFull(),
                Textarea::make('attachments_clerk')
                    ->columnSpanFull(),
                Select::make('document_shipment_type_id')
                    ->relationship('documentShipmentType', 'name'),
                Textarea::make('notes_clerk')
                    ->columnSpanFull(),
                DateTimePicker::make('ship_at'),
                Textarea::make('attachments_delivery')
                    ->columnSpanFull(),
                Textarea::make('notes_delivery')
                    ->columnSpanFull(),
                Textarea::make('attachments_orderchecker')
                    ->columnSpanFull(),
                Textarea::make('notes_orderchecker')
                    ->columnSpanFull(),
                TextInput::make('created_by')
                    ->numeric(),
                TextInput::make('created_name'),
                TextInput::make('updated_by')
                    ->numeric(),
                TextInput::make('updated_name'),
                TextInput::make('panel_name')
                    ->default('admin'),
                DateTimePicker::make('return_at'),
                DateTimePicker::make('refunded_at'),
                Toggle::make('is_cod')
                    ->required(),
                Select::make('payment_sort_id')
                    ->relationship('paymentSort', 'name'),
                TextInput::make('locked_by')
                    ->numeric(),
                DateTimePicker::make('locked_at'),
            ]);
    }
}
