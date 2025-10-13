<?php

namespace App\Core\Traits\Relationships;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToPricing
{
    /**
     * Get the pricing plan for the model
     */
    public function pricing(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Pricing\Database\Models\Pricing::class);
    }
}
