<?php

namespace App\Filament\Resources\ItemHistories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemHistoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('itemHistoryActionType.name')
                    ->label('Type')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created item' => 'primary',
                        'received' => 'primary',
                        'invoice' => 'success',
                        'adjustment' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('item.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('stock'),

                TextColumn::make('document.document_number')
                    ->label('Document #'),

                TextColumn::make('document.customer_nick')
                    ->label('FB Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('adjustment.adjustment_number')
                    ->label('Adjustment #')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Created By'),
            ])
            ->paginated([30, 60, 90, 120, 150, 180, 210])
            ->recordUrl(null)
            ->filters([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ])
            ->emptyStateActions([
                //
            ]);
    }
}
