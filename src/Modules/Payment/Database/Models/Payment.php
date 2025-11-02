<?php

namespace App\Modules\Payment\Database\Models;

use App\Core\Traits\HasTimestamps;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Currency\Database\Models\Currency;
use App\Modules\Notification\Traits\Notifiable;
class Payment extends Model
{
    use HasTimestamps, HasUuid;
    use Notifiable;
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
     * Get the payable that owns the payment.
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the currency that owns the payment.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
