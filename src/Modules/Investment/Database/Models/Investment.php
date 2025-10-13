<?php

namespace App\Modules\Investment\Database\Models;

use App\Core\Traits\HasTimestamps;
use App\Core\Traits\Relationships\BelongsToUser;
use App\Core\Traits\Relationships\BelongsToPricing;
use App\Modules\Transaction\Database\Models\Transaction;
use App\Core\Contracts\TransactionContextInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Category\Database\Models\Category;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Investment extends Model implements TransactionContextInterface
{
    use HasTimestamps;
    use BelongsToUser;
    use BelongsToPricing;

    protected $fillable = [
        'user_id',
        'pricing_id',
        'amount',
        'status',
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
        ];
    }

    /**
     * Get transaction context for the investment
     */
    public function getTransactionContext(string $operation = 'create', array $request = []): array
    {
        $config = config('investment.transaction');

        return [
            'entity_id' => $this->id,
            'amount' => $this->amount,
            'investment_type' => $this->pricing->name ?? 'unknown',
            'category_id' => $this->category_id, // For category resolution
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, 'transactable');
    }
}
