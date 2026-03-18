<?php

namespace App\Filament\Resources\Receives\Tables;

use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReceivesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('receive_number')
                    ->label('Receive Number')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('issued_at')
                    ->label('Date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('Created By'),
            ])
            ->recordUrl(null)
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
