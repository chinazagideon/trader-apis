<?php

namespace App\Modules\Payment\Database\Models;

use App\Core\Models\CoreModel;
use App\Core\Traits\HasTimestamps;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Currency\Database\Models\Currency;
use App\Modules\Notification\Traits\Notifiable;
use App\Modules\Payment\Traits\BelongsToPayable;
use App\Core\Traits\HasClientScope;
class Payment extends CoreModel
{
    use HasTimestamps;
    use HasUuid;
    use Notifiable;
    use BelongsToPayable;
    // use HasClientScope;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'method',
        'payable_type',
        'payable_id',
        'status',
        'amount',
        'currency_id',
        'client_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }


    /**
     * Get the currency that owns the payment.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
