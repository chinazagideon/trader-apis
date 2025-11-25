<?php

namespace App\Modules\Investment\Database\Models;

use App\Core\Traits\HasTimestamps;
use App\Core\Traits\Relationships\BelongsToUser;
use App\Core\Traits\Relationships\BelongsToPricing;
use App\Modules\Transaction\Database\Models\Transaction;
use App\Core\Contracts\TransactionContextInterface;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Category\Database\Models\Category;
use App\Modules\Investment\Policies\InvestmentPolicy;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Modules\Notification\Traits\Notifiable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use App\Core\Contracts\OwnershipBased;
use App\Core\Traits\LoadsRelationships;
use App\Modules\Currency\Database\Models\Currency;
use App\Modules\Pricing\Database\Models\Pricing;
use App\Modules\User\Database\Models\User;

#[UsePolicy(InvestmentPolicy::class)]
class Investment extends Model implements TransactionContextInterface, OwnershipBased
{
    use HasTimestamps;
    use BelongsToUser;
    use HasUuid;
    use Notifiable;

    protected $fillable = [
        'uuid',
        'user_id',
        'pricing_id',
        'amount',
        'status',
        'type',
        'risk',
        'name',
        'currency_id',
        'start_date',
        'end_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'type' => 'string',
            'risk' => 'string',
            'name' => 'string',
            'currency_id' => 'integer',
        ];
    }

    /**
     * Get transaction context for the investment
     */
    public function getTransactionContext(string $operation = 'create', array $request = []): array
    {
        $config = config('Investment.transaction');

        return [
            'entity_id' => $this->id,
            'amount' => $this->amount,
            'investment_type' => $this->pricing->name ?? 'unknown',
            'category_id' => $this->category_id,
            'entry_type' => $config['entry_type'],
            'status' => $config['status'],
            'narration' => $this->notes ?? 'N/A',
        ];
    }

    /**
     * Get the entity type for transaction mapping
     */
    public function getTransactionEntityType(): string
    {
        return 'investment';
    }


    /**
     * Get additional metadata for transaction creation
     */
    public function getTransactionMetadata(): array
    {
        return [
            'investment_uuid' => $this->uuid ?? null,
            'pricing_id' => $this->pricing_id,
            'user_id' => $this->user_id,
            'start_date' => $this->start_date?->toISOString(),
            'end_date' => $this->end_date?->toISOString(),
            'notes' => $this->notes,
            'category_id' => $this->category_id, // Include category_id in metadata
        ];
    }


    /**
     * Generate transaction narration [deprecated]
     */
    private function generateTransactionNarration(): string
    {
        return $this->notes ?? 'N/A';
    }

    /**
     * get transaction category
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * get currency relations
     *
     * @return BelongsTo
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * transactions model
     *
     * @return MorphOne
     */
    public function transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, 'transactable');
    }

    /**
     * get pricing relations
     *
     * @return BelongsTo
     */
    public function pricing(): BelongsTo
    {
        return $this->belongsTo(Pricing::class);
    }

    /**
     * get user relations
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
