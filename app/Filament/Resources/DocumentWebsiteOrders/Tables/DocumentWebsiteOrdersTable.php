<?php

namespace App\Filament\Resources\DocumentWebsiteOrders\Tables;

use App\Filament\Resources\Documents\DocumentResource;
use App\Models\Document;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentWebsiteOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated([30, 45, 60])
            ->columns([
                TextColumn::make('document_number')
                    ->label('Order Number')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->toggleable(),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->limit(30)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('customer_nick')
                    ->label('FB Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('items_search')
                    ->label('Items')
                    ->limit(40)
                    ->searchable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('grand_amount')
                    ->label('Total Price')
                    ->toggleable()
                    ->sortable()
                    ->prefix('P'),

                TextColumn::make('issued_at')
                    ->label('Order Date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('paymentSort.name')
                    ->label('COD')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('documentStatus.name')
                    ->label('Status')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
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
            ])
            ->recordUrl(null)
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-s-pencil-square')
                    ->color('primary')
                    ->hidden(fn (): bool => ! auth()->user()->hasRole(['Superadmin', 'Admin', 'Encoder', 'Accounting', 'Order Control Specialist', 'Customer']))
                    ->url(fn (Document $record): string => DocumentResource::getUrl('edit', ['record' => $record])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
