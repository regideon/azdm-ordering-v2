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
                
            ]);
    }
}
