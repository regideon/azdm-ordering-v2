<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReceiveItem extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function receive(): BelongsTo
    {
        return $this->belongsTo(Receive::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
