<?php

namespace App\Modules\Transaction\Policies;

use App\Core\Policies\BasePolicy;
use App\Core\Contracts\OwnershipBased;
use App\Modules\Transaction\Database\Models\Transaction;
use App\Modules\User\Database\Models\User;
use Illuminate\Database\Eloquent\Builder;

class TransactionPolicy extends BasePolicy
{
    protected string $permissionPrefix = 'transaction';

    /**
     * Apply ownership-based filtering to transactions query.
     *
     * Since Transaction is polymorphic and doesn't have a direct user_id,
     * we filter through the transactable relationship dynamically.
     */
    public function viewAnyQuery(User $user, Builder $query): Builder
    {
        // Admins see all
        if ($user->hasPermission('admin.all')) {
            return $query;
        }

        // Check if user has view_any permission
        if ($this->permissionPrefix && $user->hasPermission($this->permissionPrefix . '.view_any')) {
            return $query;
        }

        // Get allowed transactable types from config
        $allowedTypes = config('Transaction.allowed_types', []);

        // Build dynamic query for polymorphic ownership filtering
        $query->where(function ($q) use ($user, $allowedTypes) {
            foreach ($allowedTypes as $typeKey => $modelClass) {
                $q->orWhere(function ($typeQuery) use ($user, $typeKey, $modelClass) {
                    $typeQuery->where('transactable_type', $typeKey);

                    // Special handling for User model
                    if ($modelClass === User::class) {
                        $typeQuery->where('transactable_id', $user->id);
                    }
                    // For OwnershipBased models, filter by ownership column
                    elseif (in_array(OwnershipBased::class, class_implements($modelClass))) {
                        // Get ownership column - check if model has custom method
                        $ownershipColumn = $this->getModelOwnershipColumn($modelClass);

                        // Filter by the transactable's ownership column
                        $typeQuery->whereHasMorph('transactable', [$modelClass], function ($morphQuery) use ($user, $ownershipColumn) {
                            $morphQuery->where($ownershipColumn, $user->id);
                        });
                    }
                });
            }
        });

        return $query;
    }

    /**
     * Get ownership column for a model class.
     *
     * @param string $modelClass
     * @return string
     */
    protected function getModelOwnershipColumn(string $modelClass): string
    {
        // Map of model classes to their ownership columns
        // Add custom mappings here if a model uses a non-standard column
        $ownershipColumnMap = [
            User::class => 'id', // User uses 'id' as ownership column
            \App\Modules\Investment\Database\Models\Investment::class => 'user_id',
            \App\Modules\Funding\Database\Models\Funding::class => 'user_id',
            \App\Modules\Withdrawal\Database\Models\Withdrawal::class => 'user_id',
        ];

        // Return mapped column or default to 'user_id'
        return $ownershipColumnMap[$modelClass] ?? $this->ownershipColumn;
    }
}
