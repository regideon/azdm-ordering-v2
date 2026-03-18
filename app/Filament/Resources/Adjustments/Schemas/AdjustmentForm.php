<?php

namespace App\Filament\Resources\Adjustments\Schemas;

use App\Models\Adjustment;
use App\Models\Item;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdjustmentForm
{
    public static function configure(Schema $schema): Schema
    {
        $newNumber = app(Adjustment::class)->getLastNumber();

        return $schema
            ->columns(1)
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Information')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('adjustment_number')
                                        ->label('Adjustment Number')
                                        ->default(fn (?Adjustment $record) => $record?->adjustment_number ?? $newNumber)
                                        ->disabled()
                                        ->dehydrated()
                                        ->required(),

                                    DatePicker::make('issued_at')
                                        ->label('Date')
                                        ->default(now())
                                        ->required(),

                                    Select::make('adjustment_reason_id')
                                        ->label('Reason')
                                        ->relationship('adjustmentReason', 'name')
                                        ->default(1)
                                        ->required(),

                                    TextInput::make('document_id')
                                        ->label('Order ID')
                                        ->numeric()
                                        ->placeholder('e.g. Put 155161 if order number ORD-00155161')
                                        ->columnSpanFull(),

                                    MarkdownEditor::make('description')
                                        ->label('Comments')
                                        ->columnSpanFull(),
                                ]),
                            ])
                            ->columnSpanFull(),
                    ]),

                Group::make()
                    ->schema([
                        Section::make('Items')
                            ->schema([
                                Repeater::make('items')
                                    ->relationship()
                                    ->schema([
                                        Grid::make(4)->schema([
                                            Select::make('item_id')
                                                ->label('Item')
                                                ->options(Item::query()->orderBy('name')->pluck('name', 'id')->all())
                                                ->preload()
                                                ->searchable()
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(function ($state, callable $set): void {
                                                    $item = Item::query()->find($state);

                                                    if (! $item) {
                                                        return;
                                                    }

                                                    $set('item_quantity', $item->quantity);
                                                    $set('adjusted_quantity', null);
                                                    $set('operation', 0);
                                                    $set('new_quantity', $item->quantity);
                                                })
                                                ->columnSpanFull(),

                                            TextInput::make('item_quantity')
                                                ->label('Current Quantity')
                                                ->numeric()
                                                ->disabled()
                                                ->dehydrated()
                                                ->required(),

                                            Select::make('operation')
                                                ->options([
                                                    0 => 'minus',
                                                    1 => 'plus',
                                                ])
                                                ->default(0)
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(function ($state, callable $set, $get): void {
                                                    $current = (float) ($get('item_quantity') ?? 0);
                                                    $adjusted = (float) ($get('adjusted_quantity') ?? 0);
                                                    $set('new_quantity', $state ? $current + $adjusted : $current - $adjusted);
                                                }),

                                            TextInput::make('adjusted_quantity')
                                                ->label('Adjusted Quantity')
                                                ->numeric()
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(function ($state, callable $set, $get): void {
                                                    $current = (float) ($get('item_quantity') ?? 0);
                                                    $adjusted = (float) ($state ?? 0);
                                                    $set('new_quantity', ((int) ($get('operation') ?? 0)) ? $current + $adjusted : $current - $adjusted);
                                                }),

                                            TextInput::make('new_quantity')
                                                ->label('New Quantity')
                                                ->numeric()
                                                ->disabled()
                                                ->dehydrated()
                                                ->required(),
                                        ]),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
