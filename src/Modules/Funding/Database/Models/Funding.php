<?php

namespace App\Modules\Funding\Database\Models;

use App\Core\Contracts\OwnershipBased;
use App\Core\Traits\HasTimestamps;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\User\Database\Models\User;
use App\Modules\Currency\Database\Models\Currency;
use App\Modules\Funding\Enums\FundingType;
use App\Modules\Payment\Traits\HasPayments;

class Funding extends Model implements OwnershipBased
{
    use HasTimestamps, HasUuid;
    use HasPayments;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'fundable_id',
        'fundable_type',
        'amount',
        'user_id',
        'currency_id',
        'fiat_amount',
        'fiat_currency_id',
        'status',
        'notes',
        'type',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
            'fiat_amount' => 'decimal:2',
            'fiat_currency_id' => 'integer',
            'user_id' => 'integer',
            'status' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'type' => FundingType::class,
        ];
    }

    /**
     * Get the fundable that owns the funding.
     */
    public function fundable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user that owns the funding.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the currency that owns the funding.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the fiat currency that owns the funding.
     */
    public function fiatCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
