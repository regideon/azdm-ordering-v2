<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Models\CustomerClassification;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->orderBy('id', 'desc'))
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nick')
                    ->label('FB Name')
                    ->sortable()
                    ->searchable(),

                IconColumn::make('customerType.has_discount')
                    ->label('VIP')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Date Created')
                    ->date()
                    ->sortable()
                    ->toggleable(),
            ])
            ->paginated([30, 45, 60])
            ->filters([
                SelectFilter::make('customer_classification_id')
                    ->label('Customer Classification')
                    ->options(
                        CustomerClassification::query()
                            ->where('enabled', 1)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all()
                    )
                    ->placeholder('All Classifications')
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->hidden(fn (): bool => ! auth()->user()->hasRole(['Superadmin', 'Admin', 'Accounting', 'Order Control Specialist', 'Customer', 'Encoder', 'Customer Service'])),

                DeleteAction::make()
                    ->hidden(fn (): bool => ! auth()->user()->hasRole(['Superadmin', 'Admin'])),
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
