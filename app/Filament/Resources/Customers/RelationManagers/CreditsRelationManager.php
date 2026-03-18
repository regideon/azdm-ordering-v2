<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Filament\Resources\Documents\DocumentResource;
use App\Models\Credit;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CreditsRelationManager extends RelationManager
{
    protected static string $relationship = 'credits';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                TextInput::make('amount')
                    ->label('Amount')
                    ->numeric()
                    ->required()
                    ->hint('+ or -')
                    ->columnSpan(3),

                TextInput::make('description')
                    ->label('Notes')
                    ->required()
                    ->maxLength(191)
                    ->columnSpan(9),

                Hidden::make('issued_at')
                    ->default(now()),

                Hidden::make('created_by')
                    ->default(auth()->id()),

                Hidden::make('created_name')
                    ->default(auth()->user()?->name),

                Hidden::make('is_direct_create')
                    ->default(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                TextColumn::make('document_number')
                    ->label('Order number'),

                TextColumn::make('transaction_number'),

                TextColumn::make('amount')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->prefix('P')
                    ->summarize([
                        Sum::make()->label('Total Credits'),
                    ]),

                IconColumn::make('is_credit')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make('header')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['is_credit'] = ($data['amount'] ?? 0) >= 0 ? 1 : 0;

                        return $data;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),

                EditAction::make()
                    ->hidden(function (Credit $record): bool {
                        if ($record->document_id != null && $record->transaction_id != null) {
                            return true;
                        }

                        if ($record->is_direct_create) {
                            return true;
                        }

                        return false;
                    }),

                DeleteAction::make()
                    ->hidden(fn (): bool => ! auth()->user()->hasRole(['Superadmin', 'Admin', 'Encoder'])),

                Action::make('view_order')
                    ->label('View Order')
                    ->hidden(fn (Credit $record): bool => $record->document_id == null)
                    ->url(fn (?Credit $record): string => ($record && ! $record->is_direct_create && $record->document_id) ? DocumentResource::getUrl('edit', ['record' => $record->document_id]) : ''),
            ])
            ->toolbarActions([
                //
            ])
            ->emptyStateActions([
                //
            ]);
    }
}
