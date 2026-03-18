<?php

namespace App\Filament\Resources\ReportsCustomerPayments\Pages;

use App\Filament\Resources\ReportsCustomerPayments\ReportsCustomerPaymentResource;
use Filament\Resources\Pages\ListRecords;

class ListReportsCustomerPayments extends ListRecords
{
    protected static string $resource = ReportsCustomerPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
