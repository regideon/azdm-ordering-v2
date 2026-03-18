<?php

namespace App\Filament\Resources\Receives\Schemas;

use App\Models\Item;
use App\Models\Receive;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReceiveForm
{
    public static function configure(Schema $schema): Schema
    {
        $newNumber = app(Receive::class)->getLastNumber();

        return $schema
            ->columns(1)
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Items')
                            ->schema([
                                Repeater::make('items')
                                    ->relationship()
                                    ->schema([
                                        Grid::make(2)->schema([
                                            Select::make('item_id')
                                                ->label('Item')
                                                ->options(Item::query()->orderBy('name')->pluck('name', 'id')->all())
                                                ->searchable()
                                                ->required(),

                                            TextInput::make('quantity')
                                                ->label('Quantity Received')
                                                ->numeric()
                                                ->required(),
                                        ]),
                                    ]),
                            ]),
                    ]),

                Group::make()
                    ->schema([
                        Section::make('Information')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('receive_number')
                                        ->label('Receiving Number')
                                        ->default(fn (?Receive $record) => $record?->receive_number ?? $newNumber)
                                        ->disabled()
                                        ->dehydrated()
                                        ->required(),

                                    DatePicker::make('issued_at')
                                        ->label('Date')
                                        ->default(now())
                                        ->required(),

                                    MarkdownEditor::make('description')
                                        ->label('Comments')
                                        ->columnSpanFull(),
                                ]),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
