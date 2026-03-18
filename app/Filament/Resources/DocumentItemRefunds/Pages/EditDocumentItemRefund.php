<?php

namespace App\Filament\Resources\DocumentItemRefunds\Pages;

use App\Filament\Resources\DocumentItemRefunds\DocumentItemRefundResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDocumentItemRefund extends EditRecord
{
    protected static string $resource = DocumentItemRefundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
