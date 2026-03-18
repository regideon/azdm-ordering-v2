<?php

namespace App\Filament\Resources\DocumentItemCancels\Pages;

use App\Filament\Resources\DocumentItemCancels\DocumentItemCancelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDocumentItemCancels extends ListRecords
{
    protected static string $resource = DocumentItemCancelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
