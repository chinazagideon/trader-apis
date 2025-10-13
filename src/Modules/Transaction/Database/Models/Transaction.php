<?php

namespace App\Modules\Transaction\Database\Models;

use App\Core\Traits\HasTimestamps;
use App\Core\Traits\HasUuid;
use App\Modules\Transaction\Database\Models\TransactionCategory;
use App\Modules\Transaction\Traits\BelongsToTransaction;
use App\Modules\Transaction\Traits\HasTransactableTrait;
use App\Core\Models\CoreModel;
use App\Core\Contracts\TransactionContextInterface;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Payment\Database\Models\Payment;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Transaction extends CoreModel implements TransactionContextInterface
{
    use HasTimestamps, HasUuid, HasTransactableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'transactable_id',
        'transactable_type',
        'transaction_category_id',
        'narration',
        'entry_type',
        'total_amount',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected function casts(): array
    {
        return [
            'entry_type' => 'string',
            'status' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the transactable model.
     */
    public function transactable(): MorphTo
    {
        return $this->morphTo();
    }
    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    public function category()
    {
        return $this->belongsTo(TransactionCategory::class, 'transaction_category_id', 'id');
    }

    /**
     * Get the entity that is triggering the transaction.
     * For Transaction model, this is itself.
     */
    public function getTransactionEntity(): Model
    {
        return $this;
    }

    /**
     * Get the entity type for transaction mapping
     */
    public function getTransactionEntityType(): string
    {
        return 'transaction';
    }

    /**
     * Get the entity ID for transaction reference
     */
    public function getTransactionEntityId(): int
    {
        return $this->id ?? 0;
    }

    /**
     * Get transaction context for the transaction
     */
    public function getTransactionContext(string $operation = 'create', array $request = []): array
    {
        return [
            'entity_id' => $this->id,
            'amount' => $this->total_amount,
            'entry_type' => $this->entry_type,
            'status' => $this->status,
            'narration' => $this->narration,
            'transaction_category_id' => $this->transaction_category_id,
            'transactable_type' => $this->transactable_type,
            'transactable_id' => $this->transactable_id,
        ];
    }

    /**
     * Get additional metadata for transaction creation
     */
    public function getTransactionMetadata(): array
    {
        return [
            'transaction_uuid' => $this->uuid,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'transactable_type' => $this->transactable_type,
            'transactable_id' => $this->transactable_id,
        ];
    }
}
