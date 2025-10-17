<?php

namespace App\Modules\Swap\Database\Models;

use App\Core\Models\CoreModel;
use App\Core\Traits\HasTimestamps;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\User\Database\Models\User;
use App\Modules\Currency\Database\Models\Currency;

class Swap extends CoreModel
{
    use HasTimestamps, HasUuid;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'from_currency_id',
        'to_currency_id',
        'from_amount',
        'to_amount',
        'fee_amount',
        'total_amount',
        'rate',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'from_amount' => 'decimal:2',
            'to_amount' => 'decimal:2',
            'fee_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'rate' => 'decimal:2',
            'status' => 'string',
            'user_id' => 'integer',
            'from_currency_id' => 'integer',
            'to_currency_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the swap.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the from currency that owns the swap.
     */
    public function fromCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }

    /**
     * Get the to currency that owns the swap.
     */
    public function toCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }
}
