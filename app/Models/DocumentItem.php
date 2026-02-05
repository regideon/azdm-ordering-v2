<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentItem extends Model
{
    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    protected static function booted(): void
    {
        // OPTIONAL:
        // Only enable this if changes in items should affect search results.
        // If you already maintain documents.items_search via your own logic,
        // you can remove these to reduce indexing load.
        static::saved(function (self $item) {
            $item->document?->searchable();
        });

        static::deleted(function (self $item) {
            $item->document?->searchable();
        });
    }
}
