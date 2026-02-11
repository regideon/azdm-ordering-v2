<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Item extends Model
{
    use SoftDeletes;

    protected $casts = [
        'is_partial_delivery' => 'boolean',
        'enabled' => 'boolean',
        'published_at' => 'date',
    ];

    protected $guarded = [];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function categories() : BelongsToMany {
        return $this->belongsToMany(Category::class, 'category_item', 'item_id', 'category_id')->withTimestamps();
    }

    public function comments() : MorphMany {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function itemHistories() : HasMany {
        return $this->hasMany(ItemHistory::class);
    }
}
