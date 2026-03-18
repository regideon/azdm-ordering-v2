<?php

namespace App\Filament\Resources\DocumentItemCancels\Pages;

use App\Filament\Resources\DocumentItemCancels\DocumentItemCancelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDocumentItemCancel extends EditRecord
{
    protected static string $resource = DocumentItemCancelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
