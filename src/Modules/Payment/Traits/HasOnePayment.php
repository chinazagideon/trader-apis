<?php
namespace App\Modules\Payment\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Modules\Payment\Database\Models\Payment;

trait HasOnePayment
{
    /**
     * For models that have exactly one payment.
     */
    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable');
    }
}
