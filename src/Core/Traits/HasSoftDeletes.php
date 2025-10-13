<?php

namespace App\Core\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;

trait HasSoftDeletes
{
    use SoftDeletes;

    /**
     * Get formatted deleted_at timestamp
     */
    public function getDeletedAtFormattedAttribute(): ?string
    {
        return $this->deleted_at?->format('Y-m-d H:i:s');
    }

    /**
     * Check if model is soft deleted
     */
    public function isDeleted(): bool
    {
        return $this->trashed();
    }

    /**
     * Restore soft deleted model
     */
    public function restoreModel(): bool
    {
        return $this->restore();
    }
}
