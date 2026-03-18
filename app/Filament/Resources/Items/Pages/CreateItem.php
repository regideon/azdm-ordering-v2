<?php

namespace App\Filament\Resources\Items\Pages;

use App\Filament\Resources\Items\ItemResource;
use App\Models\Item;
use App\Models\ItemHistory;
use App\Support\InventoryActionType;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Schema;

class CreateItem extends CreateRecord
{
    protected static string $resource = ItemResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        /** @var Item $item */
        $item = $this->record;
        $item->subtotal = $item->price * $item->quantity;
        $item->save();

        if (! Schema::hasTable('item_histories')) {
            return;
        }

        ItemHistory::create([
            'item_id' => $item->id,
            'item_history_action_type_id' => InventoryActionType::CreatedItem->value,
            'stock' => $item->quantity,
            'created_by' => auth()->id(),
            'created_name' => auth()->user()?->name,
        ]);
    }
}
