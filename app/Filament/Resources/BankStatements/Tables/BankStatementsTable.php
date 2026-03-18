<?php

namespace App\Filament\Resources\BankStatements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class BankStatementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date_at')
                    ->label('Date')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('bankStatementBank.name')
                    ->label('Bank')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(100)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('amount')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->prefix('P'),

                IconColumn::make('is_used')
                    ->label('Is used')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('document.document_number')
                    ->label('Order Number')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('transaction.transaction_number')
                    ->label('Transaction Number')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('id')
                    ->label('ID')
                    ->formatStateUsing(fn ($state) => new HtmlString('<span style="color: #ccc;">' . $state . '</span>'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->paginated([30, 60, 90])
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
            ]);
    }
}
