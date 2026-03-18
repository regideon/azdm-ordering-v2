<?php

namespace App\Filament\Resources\DocumentItemPreorders\Tables;

use App\Filament\Resources\Documents\DocumentResource;
use App\Models\DocumentItem;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentItemPreordersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('document.document_number')
                    ->label('Order Number')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->toggleable(),

                TextColumn::make('document.documentStatus.name')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'gray',
                        'processing' => 'primary',
                        'paid' => 'success',
                        'released' => 'success',
                        'qc checked' => 'success',
                        'packed and ready to ship' => 'success',
                        'out for delivery' => 'success',
                        'order received' => 'primary',
                        'reschedule delivery' => 'warning',
                        'unsuccessful delivery attempt' => 'danger',
                        'delivered' => 'success',
                        'partial delivery' => 'danger',
                        'partial payments' => 'warning',
                        'cancelled' => 'warning',
                        'returns' => 'warning',
                        'refunds' => 'warning',
                        'out for shipping' => 'success',
                        'on-hold' => 'danger',
                        'cash on delivery' => 'success',
                        'shipped' => 'success',
                        default => 'gray',
                    }),

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

                TextColumn::make('description')
                    ->label('Description')
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
            ->defaultSort('created_at', 'desc')
            ->paginated([20, 30, 40, 50])
            ->recordUrl(null)
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('open')
                    ->url(fn (?DocumentItem $record): ?string => $record?->document ? DocumentResource::getUrl('edit', ['record' => $record->document]) : null),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
