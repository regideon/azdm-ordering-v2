<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receive extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function items(): HasMany
    {
        return $this->hasMany(ReceiveItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getLastNumber(): string
    {
        $nextNumber = (static::query()->max('id') ?? 0) + 1;

        return (string) env('PREFIX_RECEIVES', 'REC-') . str_pad((string) $nextNumber, 7, '0', STR_PAD_LEFT);
    }
}
