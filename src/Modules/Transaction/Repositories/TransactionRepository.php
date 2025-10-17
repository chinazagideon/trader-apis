<?php

namespace App\Modules\Transaction\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Transaction\Database\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionRepository extends BaseRepository
{
    public function __construct(Transaction $model)
    {
        parent::__construct($model);
    }

    /**
     * Get transactions with pagination and filters
     */
    public function getTransactions(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply filters
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('narration', 'like', "%{$filters['search']}%")
                  ->orWhere('uuid', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['entry_type'])) {
            $query->where('entry_type', $filters['entry_type']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        // Load relationships if available
        return $this->withRelationships($query)->paginate($perPage);
    }
}
