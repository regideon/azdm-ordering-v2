<?php

namespace App\Filament\Resources\ItemShowrooms\Pages;

use App\Filament\Resources\ItemShowrooms\ItemShowroomResource;
use Filament\Resources\Pages\ListRecords;

class ListItemShowrooms extends ListRecords
{
    protected static string $resource = ItemShowroomResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
