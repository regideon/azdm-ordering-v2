<?php

namespace App\Filament\Resources\Adjustments\Pages;

use App\Filament\Resources\Adjustments\AdjustmentResource;
use App\Models\Adjustment;
use App\Models\AdjustmentItem;
use App\Models\Item;
use App\Models\ItemHistory;
use App\Support\InventoryActionType;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Schema;

class CreateAdjustment extends CreateRecord
{
    protected static string $resource = AdjustmentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        /** @var Adjustment $adjustment */
        $adjustment = $this->record;
        $adjustment->created_by = auth()->id();
        $adjustment->save();

        $items = AdjustmentItem::query()->where('adjustment_id', $adjustment->id)->get();

        foreach ($items as $item) {
            $itemModel = Item::query()->findOrFail($item->item_id);
            $itemModel->quantity = $item->operation ? $itemModel->quantity + $item->adjusted_quantity : $itemModel->quantity - $item->adjusted_quantity;
            $itemModel->save();

            if (! Schema::hasTable('item_histories')) {
                continue;
            }

            ItemHistory::create([
                'item_id' => $item->item_id,
                'adjustment_id' => $adjustment->id,
                'item_history_action_type_id' => InventoryActionType::Adjustment->value,
                'stock' => $item->operation ? $item->adjusted_quantity : -$item->adjusted_quantity,
                'created_by' => auth()->id(),
            ]);
        }
    }
}
