<?php

namespace App\Core\Traits;

trait HasTimestamps
{
    /**
     * Get formatted created_at timestamp
     */
    public function getCreatedAtFormattedAttribute(): ?string
    {
        return $this->created_at?->format('Y-m-d H:i:s');
    }

    /**
     * Get formatted updated_at timestamp
     */
    public function getUpdatedAtFormattedAttribute(): ?string
    {
        return $this->updated_at?->format('Y-m-d H:i:s');
    }

    /**
     * Get human readable time difference
     */
    public function getTimeAgoAttribute(): ?string
    {
        return $this->created_at?->diffForHumans();
    }
}
