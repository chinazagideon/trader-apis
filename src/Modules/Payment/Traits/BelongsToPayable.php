<?php
namespace App\Modules\Payment\Traits;

use Illuminate\Database\Eloquent\Relations\MorphTo;

trait BelongsToPayable
{
    /**
     * Inverse of the payable relation on Payment model.
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }
}
