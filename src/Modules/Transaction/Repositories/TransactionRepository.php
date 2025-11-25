<?php

namespace App\Modules\Transaction\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Transaction\Database\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Core\Traits\LoadsMorphRelations;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;

class TransactionRepository extends BaseRepository
{

    use HasRelationships;
    use LoadsMorphRelations;
    /**
     * Constructor
     * @param Transaction $model
     */
    public function __construct(Transaction $model)
    {
        parent::__construct($model);
    }

    /**
     * Get default relationships for the transaction model
     */
    protected function getDefaultRelationships(): array
    {
        return ['transactable', 'payment'];
    }

    /**
     * Get transactions with pagination and filters
     */
    // public function getTransactions(array $filters = [], int $perPage = 15): LengthAwarePaginator
    // {
    //     $query = $this->model->newQuery();
    //     $this->applyBusinessFilters($query, $filters);
    //     return $this->withRelationships($query)->paginate($perPage);
    // }

     /**
     * Get the payments
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getTransactions(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->queryWithPolicyFilter($filters);
        $query = $this->withAllRelations($query, $this->getDefaultRelationships());
        $query->latest();
        return $query->paginate($perPage);
    }
}
