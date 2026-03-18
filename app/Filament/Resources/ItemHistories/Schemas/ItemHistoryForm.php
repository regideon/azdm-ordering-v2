<?php

namespace App\Filament\Resources\ItemHistories\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;

class ItemHistoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Group::make()
                    ->schema([
                        Grid::make(4)->schema([
                            DatePicker::make('created_at')
                                ->label('Date'),

                            Select::make('item_history_action_type_id')
                                ->relationship('itemHistoryActionType', 'name')
                                ->label('Type'),

                            Select::make('item_id')
                                ->relationship('item', 'name')
                                ->label('Item'),

                            TextInput::make('stock')
                                ->label('Stock'),

                            Select::make('document_id')
                                ->relationship('document', 'document_number')
                                ->label('Document #'),

                            Select::make('adjustment_id')
                                ->relationship('adjustment', 'adjustment_number')
                                ->label('Adjustment #'),

                            TextInput::make('created_name')
                                ->label('Created By'),
                        ]),
                    ]),
            ]);
    }
}
