<?php

namespace App\Modules\Payment\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Payment\Database\Models\Payment;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Core\Traits\LoadsRelationships;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use App\Core\Contracts\MorphRepositoryInterface;
use App\Core\Traits\LoadsMorphRelations;

class PaymentRepository extends BaseRepository implements MorphRepositoryInterface
{
    use HasRelationships;
    use LoadsMorphRelations;
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
        return ['currency', 'payable'];
    }

    // /**
    //  * Get the payments
    //  * @param array $filters
    //  * @param int $perPage
    //  * @return LengthAwarePaginator
    //  */
    public function getPayments(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->queryWithPolicyFilter($filters);
        $query = $this->withAllRelations($query, $this->getDefaultRelationships());
        $query->latest('created_at');
        return $query->paginate($perPage);
    }

    /**
     * Get a single payment with relationships loaded
     * Uses the same relationship loading as getPayments() for consistency
     *
     * @param int $id
     * @return Payment|null
     */
    public function getPayment(int $id): ?Payment
    {
        $query = $this->queryWithPolicyFilter(['id' => $id]);
        $query = $this->withAllRelations($query, $this->getDefaultRelationships());
        return $query->first();
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

    /**
     * Get the relationships to load for the payable model
     * @return array
     */
    public function morphToRelations(): array
    {
        return [
            \App\Modules\Withdrawal\Database\Models\Withdrawal::class => ['withdrawable', 'fiatCurrency'],
            \App\Modules\Funding\Database\Models\Funding::class => ['fundable', 'fiatCurrency'],
        ];
    }

    /**
     * Get the name of the morph relationship
     * This is used to load the relationships for the payable model
     * @return string
     */
    public function getMorphRelationshipName(): string
    {
        return 'payable';
    }
}
