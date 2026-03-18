<?php

namespace App\Filament\Resources\DocumentItems\Pages;

use App\Filament\Resources\DocumentItems\DocumentItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDocumentItem extends EditRecord
{
    protected static string $resource = DocumentItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
