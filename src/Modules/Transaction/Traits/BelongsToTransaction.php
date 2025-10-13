<?php

namespace App\Modules\Transaction\Traits;

use App\Modules\Transaction\Database\Models\Transaction;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTransaction
{
    /**
     * Get the transaction that owns the model
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
