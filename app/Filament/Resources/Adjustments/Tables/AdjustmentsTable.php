<?php

namespace App\Filament\Resources\Adjustments\Tables;

use App\Models\Adjustment;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdjustmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('adjustment_number')
                    ->label('Adjustment Number')
                    ->description(fn (Adjustment $record): string => $record->adjustmentReason?->name ?? '')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('issued_at')
                    ->label('Date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('document.document_number')
                    ->label('Document #'),

                TextColumn::make('user.name')
                    ->label('Created By'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ]);
    }
}
