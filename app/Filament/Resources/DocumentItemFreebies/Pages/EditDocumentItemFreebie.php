<?php

namespace App\Filament\Resources\DocumentItemFreebies\Pages;

use App\Filament\Resources\DocumentItemFreebies\DocumentItemFreebieResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDocumentItemFreebie extends EditRecord
{
    protected static string $resource = DocumentItemFreebieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
