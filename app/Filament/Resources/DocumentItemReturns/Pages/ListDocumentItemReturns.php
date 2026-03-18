<?php

namespace App\Filament\Resources\DocumentItemReturns\Pages;

use App\Filament\Resources\DocumentItemReturns\DocumentItemReturnResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDocumentItemReturns extends ListRecords
{
    protected static string $resource = DocumentItemReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
