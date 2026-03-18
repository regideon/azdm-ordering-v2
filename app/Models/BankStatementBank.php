<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankStatementBank extends Model
{
    public function bankStatements(): HasMany
    {
        return $this->hasMany(BankStatement::class);
    }
}
