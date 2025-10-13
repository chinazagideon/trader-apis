<?php

namespace App\Core\Traits\Relationships;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToUser
{
    /**
     * Get the user that owns the model
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Database\Models\User::class);
    }
}
