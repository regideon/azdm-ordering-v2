<?php

namespace App\Filament\Resources\DocumentItems\Pages;

use App\Filament\Resources\DocumentItems\DocumentItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDocumentItems extends ListRecords
{
    protected static string $resource = DocumentItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
