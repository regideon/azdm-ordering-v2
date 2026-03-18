<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Filament\Resources\Documents\DocumentResource;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Orders';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('document_number')
            ->columns([
                TextColumn::make('document_number')
                    ->label('Order Number')
                    ->searchable()
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
                        default => 'gray',
                    }),

                TextColumn::make('grand_amount')
                    ->label('Total Price')
                    ->toggleable()
                    ->sortable()
                    ->prefix('P')
                    ->summarize([
                        Sum::make(),
                    ]),

                TextColumn::make('issued_at')
                    ->label('Order Date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View Order')
                    ->url(fn ($record): string => DocumentResource::getUrl('edit', ['record' => $record])),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
