<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemHistory extends Model
{
    public function item() : BelongsTo {
        return $this->belongsTo(Item::class);
    }

    public function document() : BelongsTo {
        return $this->belongsTo(Document::class);
    }

    public function adjustment() : BelongsTo {
        return $this->belongsTo(Adjustment::class);
    }

    public function user() : BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function itemHistoryActionType() : BelongsTo {
        return $this->belongsTo(ItemHistoryActionType::class);
    }
}
