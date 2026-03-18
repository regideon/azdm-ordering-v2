<?php

namespace App\Filament\Resources\ReportsCustomerPayments\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HtmlString;

class ReportsCustomerPaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
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

                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('name', 'asc')
            ->paginated([50, 100, 150, 200, 250, 300])
            ->recordUrl(null)
            ->filters([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('reports_customer_payment')
                        ->label('Customer Payments')
                        ->icon('heroicon-m-presentation-chart-line')
                        ->action(function (Collection $records): void {
                            $customerIds = $records->pluck('id')->all();
                            $url = url('/reports-customer-payment/' . implode('-', $customerIds));

                            Notification::make()
                                ->iconColor('primary')
                                ->icon('heroicon-m-presentation-chart-line')
                                ->title('Customer Payment Reports')
                                ->body(new HtmlString('Click on the following link <a href="' . e($url) . '" style="color: #4f46e5;" target="_blank">' . e($url) . '</a>'))
                                ->persistent()
                                ->send();
                        }),
                ]),
            ]);
    }
}
