<?php

namespace App\Filament\Resources\DocumentItemOffsets\Pages;

use App\Filament\Resources\DocumentItemOffsets\DocumentItemOffsetResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDocumentItemOffset extends EditRecord
{
    protected static string $resource = DocumentItemOffsetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
