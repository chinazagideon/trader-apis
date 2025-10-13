<?php

namespace App\Modules\Category\Database\Models;

use App\Core\Traits\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasTimestamps;

    protected $fillable = [
        'name',
        'description',
        'type',
        'color',
        'icon',
        'status',
        'created_by',
        'entity_types', // JSON field for supported entity types
        'operations',   // JSON field for supported operations
        'metadata',     // JSON field for additional data
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'entity_types' => 'array',
            'operations' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * Get transaction categories for this category
     */
    public function transactionCategories(): HasMany
    {
        return $this->hasMany(\App\Modules\Transaction\Database\Models\TransactionCategory::class);
    }

    /**
     * Check if this category supports a specific entity type
     */
    public function supportsEntityType(string $entityType): bool
    {
        $supportedTypes = $this->entity_types ?? [];
        return empty($supportedTypes) || in_array($entityType, $supportedTypes);
    }

    /**
     * Check if this category supports a specific operation
     */
    public function supportsOperation(string $operation): bool
    {
        $supportedOperations = $this->operations ?? [];
        return empty($supportedOperations) || in_array($operation, $supportedOperations);
    }

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for categories supporting specific entity type
     */
    public function scopeForEntityType($query, string $entityType)
    {
        return $query->where(function ($q) use ($entityType) {
            $q->whereNull('entity_types')
              ->orWhereJsonContains('entity_types', $entityType);
        });
    }

    /**
     * Scope for categories supporting specific operation
     */
    public function scopeForOperation($query, string $operation)
    {
        return $query->where(function ($q) use ($operation) {
            $q->whereNull('operations')
              ->orWhereJsonContains('operations', $operation);
        });
    }
}
