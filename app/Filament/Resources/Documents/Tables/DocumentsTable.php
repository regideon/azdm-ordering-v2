<?php

namespace App\Filament\Resources\Documents\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ForceDeleteBulkAction;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchOnBlur()
            ->columns([
                TextColumn::make('document_number')
                    ->label('Order Number')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->toggleable(),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->limit(20)
                    ->tooltip(fn ($state) => $state)
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('customer_nick')
                    ->label('FB Name')
                    ->limit(20)
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->toggleable(),

                // TextColumn::make('items_search')
                //     ->limit(40)
                //     ->searchable()
                //     ->weight(FontWeight::Bold)
                //     ->label('Items'),
            
                TextColumn::make('items_search')
                    ->label('Items')
                    // FULLTEXT search implementation using MATCH AGAINST
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search) {
                        return $query->whereRaw(
                            'MATCH(items_search) AGAINST (? IN BOOLEAN MODE)',
                            [$search . '*']
                        );
                    })
                    ->limit(40)
                    ->toggleable(),
                
                TextColumn::make('customer_phone')
                    ->label('Customer phone')
                    ->limit(12)
                    ->toggleable(),

                TextColumn::make('customer_address')
                    ->label('Customer address')
                    ->limit(30)
                    ->toggleable(),

                TextColumn::make('grand_amount')
                    ->label('Total Price')
                    ->toggleable()
                    ->prefix('₱'),

                TextColumn::make('issued_at')
                    ->label('Order Date') // Invoice Date
                    ->date()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('paymentSort.name')
                    ->label('COD')
                    ->toggleable()
                    ->color('primary')
                    ->badge(),

                TextColumn::make('banks')
                    ->label('Banks')
                    ->state(fn ($record) => $record->bankStatementBanks->pluck('name')->all()) // array
                    // ->color(fn (string $state): string => match ($state) {
                    //     'BDO' => 'primary',
                    //     'CHINABANK' => 'danger',
                    //     'BPI' => 'warning',
                    //     'PAYPAL' => 'gray',
                    //     default => 'primary', // Default color if no match
                    // })
                    ->color('primary')
                    ->badge(),
                

                TextColumn::make('documentStatus.name')
                    ->label('Status')
                    // ->color(fn (string $state): string => match ($state) {
                    //     'new' => 'gray',
                    //     'processing' => 'primary',
                    //     'paid' => 'success',
                    //     'released' => 'success',
                    //     'qc checked' => 'success',
                    //     'packed and ready to ship' => 'success',
                    //     'out for delivery' => 'success',

                    //     'order received' => 'primary',
                    //     'reschedule delivery' => 'warning',
                    //     'unsuccessful delivery attempt' => 'danger',

                    //     'delivered' => 'success',

                    //     'partial delivery' => 'danger',
                    //     'partial payments' => 'warning',

                    //     'cancelled' => 'warning',
                    //     'returns' => 'warning',
                    //     'refunds' => 'warning',

                    //     'out for shipping' => 'success',

                    //     'on-hold' => 'danger',
                    //     'cash on delivery' => 'success',

                    //     'shipped' => 'success',
                    // })
                    ->color('primary')
                    ->badge(),


                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
          
                
            ])
            ->defaultSort('id', 'desc')
            ->paginated([20, 30, 40])
            ->filters([
                //
            ])
            ->recordActions([
                // \Filament\Actions\Action::make('viewUser')
                //     ->schema([
                //     ]),

                ViewAction::make()
                    ->modal()
                    ->modalHeading('View Order')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalWidth('5xl'),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
