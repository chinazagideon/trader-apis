<?php

namespace App\Modules\Payment\Database\Models;

use App\Core\Models\CoreModel;
use App\Core\Traits\HasTimestamps;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Core\Traits\HasClientApp;
use App\Core\Traits\HasClientScope;

class PaymentProcessor extends CoreModel
{
    use HasTimestamps;
    use HasUuid;
    use HasClientApp;
    use HasClientScope;

    protected $fillable = [
        'uuid',
        'payment_gateway_id',
        'payment_id',
        'amount',
        'fee',
        'total_amount',
        'market_rate',
        'fiat_amount',
        'fiat_currency',
        'currency',
        'status',
        'processor_data',
        'payment_hash',
        'client_id',
    ];

    protected function casts(): array
    {
        return [
            'processor_data' => 'array',
            'status' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'market_rate' => 'decimal:8',
            'fiat_amount' => 'decimal:2',
            'fiat_currency' => 'string',
            'currency' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the payment gateway that owns the payment processor.
     */
    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    /**
     * Get the payment that owns the payment processor.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

}
