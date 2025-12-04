<?php

namespace App\Modules\Withdrawal\Database\Models;

use App\Core\Traits\HasTimestamps;
use App\Core\Traits\HasUuid;
use App\Modules\User\Database\Models\User;
use App\Modules\Payment\Database\Models\Payment;
use App\Modules\Currency\Database\Models\Currency;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use App\Core\Contracts\HasStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Core\Contracts\OwnershipBased;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Modules\Withdrawal\Enums\WithdrawalTypes;
use App\Modules\Payment\Traits\HasPayments;
use App\Core\Traits\HasClientScope;
use App\Core\Traits\HasClientApp;
class Withdrawal extends Model implements HasStatus, OwnershipBased
{
    use HasTimestamps;
    use HasUuid;
    use HasPayments;
    use HasClientScope;
    use HasClientApp;


    protected $fillable = [
        'uuid',
        'user_id',
        'withdrawable_id',
        'withdrawable_type',
        'amount',
        'currency_id',
        'fiat_amount',
        'fiat_currency_id',
        'type',
        'status',
        'notes',
        'client_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
            'fiat_amount' => 'decimal:2',
            'status' => 'string',
            'type' => WithdrawalTypes::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the withdrawal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the withdrawable that owns the withdrawal.
     */
    public function withdrawable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the currency that owns the withdrawal.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the fiat currency that owns the withdrawal.
     */
    public function fiatCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'fiat_currency_id');
    }

    /**
     * Get the column name for the status
     */
    public function getStatusColumn(): string
    {
        return 'status';
    }

    /**
     * Get the allowed statuses for moderators
     */
    public function getModeratorAllowedStatuses(): array
    {
        return ['pending', 'cancelled', 'completed'];
    }

    /**
     * Get the label for the withdrawal type
     * @return string
     */
    public function getTypeLabel(): string
    {
        return $this->type ? WithdrawalTypes::from($this->type)->label() : $this->type;
    }
}
