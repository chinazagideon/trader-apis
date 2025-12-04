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
use App\Core\Traits\HasClientApp;
use App\Core\Contracts\TransactionContextInterface;
use App\Modules\Category\Enums\CategoryType;

class Payment extends CoreModel implements TransactionContextInterface
{
    use HasTimestamps;
    use HasUuid;
    use Notifiable;
    use BelongsToPayable;
    use HasClientScope;
    use HasClientApp;


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
            'amount' => 'decimal:8',
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

    /**
     * Get transaction context for the investment
     */
    public function getTransactionContext(string $operation = 'create', array $request = []): array
    {
        return [
            'entity_id' => $this->id,
            'amount' => $this->amount,
            'payment_method' => $this->method,
            'category_id' => CategoryType::Payment->value,
            'entry_type' => $this->getEntryType(),
            'status' => $this->status,
            'currency_id' => $this->currency_id,
        ];
    }

    /**
     * Get the entity type for transaction mapping
     */
    public function getTransactionEntityType(): string
    {
        return 'payment';
    }

    /**
     * Get additional metadata for transaction creation
     */
    public function getTransactionMetadata(): array
    {
        return [
            'payment_uuid' => $this->uuid,
            'entry_type' => $this->getEntryType(),
            'status' => $this->status,
        ];
    }

    /**
     * Get the entry type for the payment
     * @return string
     */
    private function getEntryType(): string
    {
        return match($this->payable_type) {
            'funding' => 'credit',
            'withdrawal' => 'debit',
            default => 'unknown',
        };
    }
}
