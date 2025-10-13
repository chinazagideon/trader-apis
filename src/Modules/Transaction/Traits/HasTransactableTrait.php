<?php

namespace App\Modules\Transaction\Traits;

use Illuminate\Database\Eloquent\Relations\MorphTo;

trait HasTransactableTrait
{
    /**
     * Get the transactable model.
     * @return MorphTo
     */
    public function transactable(): MorphTo
    {
        return $this->morphTo('transactable', 'transactable_type', 'transactable_id');
    }
}
