<?php

namespace App\Filament\Resources\DocumentItemReturns\Pages;

use App\Filament\Resources\DocumentItemReturns\DocumentItemReturnResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDocumentItemReturn extends EditRecord
{
    protected static string $resource = DocumentItemReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
