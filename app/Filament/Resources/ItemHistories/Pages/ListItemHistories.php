<?php

namespace App\Filament\Resources\ItemHistories\Pages;

use App\Filament\Resources\ItemHistories\ItemHistoryResource;
use Filament\Resources\Pages\ListRecords;

class ListItemHistories extends ListRecords
{
    protected static string $resource = ItemHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
