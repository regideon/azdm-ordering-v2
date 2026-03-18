<?php

namespace App\Filament\Resources\Items\Tables;

use App\Models\Item;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HtmlString;

class ItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated([30, 45, 60])
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('price')
                    ->label('Price')
                    ->state(fn (Item $record) => 'P' . number_format((float) $record->price, 2))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Quantity')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('subtotal')
                    ->state(fn (Item $record) => 'P' . number_format((float) ($record->price * $record->quantity), 2))
                    ->summarize(Sum::make())
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('purchase_price')
                    ->label('Old Cost Price')
                    ->searchable()
                    ->hidden(fn (): bool => ! auth()->user()->hasRole(['Superadmin', 'Admin']))
                    ->sortable(),

                TextColumn::make('purchase_price_new')
                    ->label('New Cost Price')
                    ->searchable()
                    ->hidden(fn (): bool => ! auth()->user()->hasRole(['Superadmin', 'Admin']))
                    ->sortable(),

                TextColumn::make('supplier_name')
                    ->label('Supplier name')
                    ->searchable()
                    ->hidden(fn (): bool => ! auth()->user()->hasRole(['Superadmin', 'Admin']))
                    ->sortable(),

                IconColumn::make('enabled')
                    ->label('Visibility')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('security_stock')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('published_at')
                    ->label('Publish Date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_partial_delivery')
                    ->label('Partial Delivery')
                    ->trueLabel('Only Partial Delivery')
                    ->falseLabel('Not Partial Delivery')
                    ->native(false),

                TernaryFilter::make('enabled')
                    ->label('Enabled')
                    ->trueLabel('Only Enabled')
                    ->falseLabel('Only Hidden')
                    ->native(false),
            ])
            ->recordActions([
                Action::make('is_archived')
                    ->requiresConfirmation()
                    ->label('Old Item')
                    ->icon('heroicon-m-archive-box-x-mark')
                    ->color('warning')
                    ->action(function (Item $record): void {
                        $record->is_archived = 1;
                        $record->save();
                    }),

                ViewAction::make(),

                EditAction::make()
                    ->hidden(fn (): bool => ! auth()->user()->hasRole(['Superadmin', 'Admin', 'Accounting', 'Order Control Specialist', 'Customer'])),

                DeleteAction::make()
                    ->hidden(fn (): bool => ! auth()->user()->hasRole(['Superadmin', 'Admin'])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('export_selected_items')
                        ->label('Export Selected Items')
                        ->icon('heroicon-m-bolt')
                        ->action(function (Collection $records): void {
                            Notification::make()
                                ->title('Export Selected Items')
                                ->body(new HtmlString('Selected item count: <b>' . $records->count() . '</b>'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ]);
    }
}
