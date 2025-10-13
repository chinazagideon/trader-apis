<?php

namespace App\Modules\Investment\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Investment\Database\Models\Investment;
use Illuminate\Pagination\LengthAwarePaginator;

class InvestmentRepository extends BaseRepository
{
    public function __construct(Investment $model)
    {
        parent::__construct($model);
    }


    /**
     * Get investments with pagination
     */
    public function getInvestments(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query();

        // Apply filters
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['pricing_id'])) {
            $query->where('pricing_id', $filters['pricing_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['start_date'])) {
            $query->where('start_date', $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $query->where('end_date', $filters['end_date']);
        }
        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        return $this->withRelationships($query)->paginate($perPage);
    }
}
