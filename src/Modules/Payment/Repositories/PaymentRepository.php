<?php

namespace App\Modules\Payment\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Payment\Database\Models\Payment;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentRepository extends BaseRepository
{
    /**
     * Constructor
     * @param Payment $model
     */
    public function __construct(Payment $model)
    {
        parent::__construct($model);
    }

    /**
     * Get default relationships for the payment model
     */
    protected function getDefaultRelationships(): array
    {
        return ['payable', 'currency'];
    }

    /**
     * Get the payments
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPayments(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query();
        $this->applyFilters($query, $filters);
        return $this->withRelationships($query, $this->getDefaultRelationships())->paginate($perPage);
    }

    /**
     * Find a payment by id
     * @param int $id
     * @return Payment
     */
    public function findOrFail(int $id): Payment
    {
        return $this->model->findOrFail($id);
    }
}
