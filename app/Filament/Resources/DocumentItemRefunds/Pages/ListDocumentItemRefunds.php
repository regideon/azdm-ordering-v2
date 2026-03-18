<?php

namespace App\Filament\Resources\DocumentItemRefunds\Pages;

use App\Filament\Resources\DocumentItemRefunds\DocumentItemRefundResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDocumentItemRefunds extends ListRecords
{
    protected static string $resource = DocumentItemRefundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
