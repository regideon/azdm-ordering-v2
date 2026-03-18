<?php

namespace App\Filament\Resources\DocumentItemFreebies\Pages;

use App\Filament\Resources\DocumentItemFreebies\DocumentItemFreebieResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDocumentItemFreebies extends ListRecords
{
    protected static string $resource = DocumentItemFreebieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
