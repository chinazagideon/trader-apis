<?php

namespace App\Modules\Payment\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Modules\Payment\Database\Models\Payment;
trait HasPayments
{

    /**
     * Get the payments for the payable model.
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\App\Modules\Payment\Database\Models\Payment>
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }


}
