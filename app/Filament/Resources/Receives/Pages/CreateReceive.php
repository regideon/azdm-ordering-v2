<?php

namespace App\Filament\Resources\Receives\Pages;

use App\Filament\Resources\Receives\ReceiveResource;
use App\Models\Item;
use App\Models\ItemHistory;
use App\Models\Receive;
use App\Models\ReceiveItem;
use App\Support\InventoryActionType;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Schema;

class CreateReceive extends CreateRecord
{
    protected static string $resource = ReceiveResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        /** @var Receive $receive */
        $receive = $this->record;
        $receive->created_by = auth()->id();
        $receive->save();

        $items = ReceiveItem::query()->where('receive_id', $receive->id)->get();

        foreach ($items as $item) {
            $itemModel = Item::query()->findOrFail($item->item_id);
            $itemModel->quantity = $itemModel->quantity + $item->quantity;
            $itemModel->save();

            if (! Schema::hasTable('item_histories')) {
                continue;
            }

            ItemHistory::create([
                'item_id' => $item->item_id,
                'adjustment_id' => $receive->id,
                'item_history_action_type_id' => InventoryActionType::Received->value,
                'stock' => $item->quantity,
                'created_by' => auth()->id(),
            ]);
        }
    }
}
