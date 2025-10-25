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

class Withdrawal extends Model implements HasStatus, OwnershipBased
{
    use HasTimestamps, HasUuid;
    use HasRelationships;

    protected $fillable = [
        'uuid',
        'user_id',
        'payment_id',
        'amount',
        'currency_id',
        'status',
        'notes',
    ];

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
     * Get the user that owns the withdrawal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payment that owns the withdrawal.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the currency that owns the withdrawal.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
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
}
