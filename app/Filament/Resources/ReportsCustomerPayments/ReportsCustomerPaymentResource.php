<?php

namespace App\Filament\Resources\ReportsCustomerPayments;

use App\Filament\Resources\ReportsCustomerPayments\Pages\ListReportsCustomerPayments;
use App\Filament\Resources\ReportsCustomerPayments\Tables\ReportsCustomerPaymentsTable;
use App\Models\Customer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ReportsCustomerPaymentResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $title = 'Customer Payments';

    public static function getNavigationLabel(): string
    {
        return 'Customer Payments';
    }

    public static function getNavigationSort(): ?int
    {
        return 250;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Reports';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return ReportsCustomerPaymentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReportsCustomerPayments::route('/'),
        ];
    }
}
