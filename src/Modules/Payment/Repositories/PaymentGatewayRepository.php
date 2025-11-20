<?php

namespace App\Modules\Payment\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Payment\Database\Models\PaymentGateway;
use App\Core\Exceptions\AppException;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentGatewayRepository extends BaseRepository
{
    protected string $serviceName = 'PaymentGatewayRepository';

    /**
     * Constructor
     * @param PaymentGateway $model
     */
    public function __construct(PaymentGateway $model)
    {
        parent::__construct($model);
    }

    /**
     * Find payment gateway by slug
     * @param string $slug
     * @param array $columns
     * @param array $filters
     * @return PaymentGateway|null
     */
    public function findBySlug(string $slug, array $columns = ['*'], array $filters = []): ?PaymentGateway
    {
        return $this->model->where('slug', $slug)
            ->when(!empty($filters), function ($query) use ($filters) {
                foreach ($filters as $key => $value) {
                    if ($value !== null && $value !== '') {
                        $query->where($key, $value);
                    }
                }
            })
            ->first($columns);
    }

    /**
     * Find payment gateway by id
     * @param int $id
     * @param array $columns
     * @return PaymentGateway|null
     */
    public function findById(int $id, array $columns = ['*']): ?PaymentGateway
    {
        return $this->find($id, $columns);
    }

    /**
     * Get default relationships for the payment gateway model
     */
    protected function getDefaultRelationships(): array
    {
        return [];
    }

    /**
     * Disable payment gateways with the same slug (excluding current record if provided)
     * @param string $slug
     * @param int|null $excludeId ID to exclude from disabling (for update operations)
     * @return int Number of records updated
     */
    public function disablePaymentGateway(string $slug, ?int $excludeId = null): int
    {
        $query = $this->queryWithPolicyFilter()
            ->where('slug', $slug)
            ->where('is_active', true);

        // Exclude current record during update operations
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        // Use bulk update for better performance
        return $query->update(['is_active' => false]);
    }

    /**
     * Get payment gateways with filters and pagination
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaymentGateways(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->queryUnfiltered();
        $query = $this->applyFilters($query, $filters);
        return $this->withRelationships($query, $this->getDefaultRelationships())->paginate($perPage);
    }
}
