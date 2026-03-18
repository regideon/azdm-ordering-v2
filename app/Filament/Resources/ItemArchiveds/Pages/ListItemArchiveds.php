<?php

namespace App\Filament\Resources\ItemArchiveds\Pages;

use App\Filament\Resources\ItemArchiveds\ItemArchivedResource;
use Filament\Resources\Pages\ListRecords;

class ListItemArchiveds extends ListRecords
{
    protected static string $resource = ItemArchivedResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
