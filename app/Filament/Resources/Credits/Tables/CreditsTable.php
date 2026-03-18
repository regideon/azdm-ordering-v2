<?php

namespace App\Filament\Resources\Credits\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CreditsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->limit(30)
                    ->searchable()
                    ->toggleable(),

                IconColumn::make('is_direct_create')
                    ->label('Direct Adding')
                    ->toggleable()
                    ->boolean(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->toggleable()
                    ->numeric()
                    ->summarize(Sum::make()->label('Remaining Money')->money('PHP'))
                    ->prefix('P'),

                TextColumn::make('document_number')
                    ->label('Order Number')
                    ->toggleable(),

                TextColumn::make('created_name')
                    ->label('Updated By'),

                TextColumn::make('description')
                    ->label('Comment'),
            ])
            ->defaultGroup('customer_id')
            ->paginated([30, 45, 60])
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
            ]);
    }
}
