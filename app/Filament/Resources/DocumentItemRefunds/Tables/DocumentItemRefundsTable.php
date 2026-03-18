<?php

namespace App\Filament\Resources\DocumentItemRefunds\Tables;

use App\Filament\Resources\Documents\DocumentResource;
use App\Models\DocumentItem;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentItemRefundsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated([30, 45, 60])
            ->columns([
                TextColumn::make('document.document_number')
                    ->label('Order Number')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->toggleable(),

                TextColumn::make('document.customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('document.customer.nick')
                    ->label('FB Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Item')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('quantity')
                    ->label('Qty')
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Price')
                    ->sortable(),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('document.issued_at')
                    ->label('Order Date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
            ])
            ->recordUrl(null)
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('open')
                    ->url(fn (DocumentItem $record): ?string => $record->document ? DocumentResource::getUrl('edit', ['record' => $record->document]) : null),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ]);
    }
}
