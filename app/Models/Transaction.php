<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function bankStatement(): BelongsTo
    {
        return $this->belongsTo(BankStatement::class);
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function generateNewNumber(): string
    {
        $nextNumber = (static::query()->max('id') ?? 0) + 1;

        return (string) env('PREFIX_TRANSACTIONS', 'TRA-') . str_pad((string) $nextNumber, 7, '0', STR_PAD_LEFT);
    }
}
