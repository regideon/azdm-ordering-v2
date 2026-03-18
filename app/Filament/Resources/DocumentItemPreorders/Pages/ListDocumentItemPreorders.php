<?php

namespace App\Filament\Resources\DocumentItemPreorders\Pages;

use App\Filament\Resources\DocumentItemPreorders\DocumentItemPreorderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDocumentItemPreorders extends ListRecords
{
    protected static string $resource = DocumentItemPreorderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
