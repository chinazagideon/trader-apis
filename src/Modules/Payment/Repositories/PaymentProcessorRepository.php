<?php

namespace App\Modules\Payment\Repositories;

use App\Core\Contracts\MorphRepositoryInterface;
use App\Core\Repositories\BaseRepository;
use App\Modules\Payment\Database\Models\PaymentProcessor;
use App\Core\Traits\LoadsMorphRelations;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Core\Traits\LoadsRelationships;

class PaymentProcessorRepository extends BaseRepository
{

    use LoadsRelationships;
    protected string $serviceName = 'PaymentProcessorRepository';

    public function __construct(PaymentProcessor $model)
    {
        parent::__construct($model);
    }

    /**
     * Get default relationships for the payment processor model
     */
    protected function getDefaultRelationships(): array
    {
        return ['payment', 'paymentGateway'];
    }

    /**
     * Get payment processors with filters and pagination
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaymentProcessors(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->queryWithPolicyFilter($filters);
        $query = $this->withRelationships($query, $this->getDefaultRelationships());
        return $query->paginate($perPage);
    }
}
