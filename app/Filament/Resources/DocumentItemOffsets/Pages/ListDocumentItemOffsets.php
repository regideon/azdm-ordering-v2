<?php

namespace App\Filament\Resources\DocumentItemOffsets\Pages;

use App\Filament\Resources\DocumentItemOffsets\DocumentItemOffsetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDocumentItemOffsets extends ListRecords
{
    protected static string $resource = DocumentItemOffsetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
