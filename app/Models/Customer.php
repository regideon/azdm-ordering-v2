<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use SoftDeletes;

    public function credits() : HasMany {
        return $this->hasMany(Credit::class);
    }

    public function documents() : HasMany {
        return $this->hasMany(Document::class);
    }

    public function customerType() : BelongsTo {
        return $this->belongsTo(CustomerType::class);
    }

    public function customerClassification() : BelongsTo {
        return $this->belongsTo(CustomerClassification::class);
    }
}
