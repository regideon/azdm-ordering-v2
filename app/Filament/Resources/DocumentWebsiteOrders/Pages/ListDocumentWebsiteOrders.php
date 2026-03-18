<?php

namespace App\Filament\Resources\DocumentWebsiteOrders\Pages;

use App\Filament\Resources\DocumentWebsiteOrders\DocumentWebsiteOrdersResource;
use Filament\Resources\Pages\ListRecords;

class ListDocumentWebsiteOrders extends ListRecords
{
    protected static string $resource = DocumentWebsiteOrdersResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
