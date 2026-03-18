<?php

namespace App\Filament\Resources\DocumentItemPreorders\Pages;

use App\Filament\Resources\DocumentItemPreorders\DocumentItemPreorderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDocumentItemPreorder extends EditRecord
{
    protected static string $resource = DocumentItemPreorderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
