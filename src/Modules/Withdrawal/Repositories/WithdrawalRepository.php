<?php

namespace App\Modules\Withdrawal\Repositories;

use App\Modules\Withdrawal\Database\Models\Withdrawal;
use App\Core\Repositories\BaseRepository;
use App\Core\Traits\FiltersResponsesByOwnership;
use App\Modules\User\Enums\RolesEnum;
use Illuminate\Pagination\LengthAwarePaginator;

class WithdrawalRepository extends BaseRepository
{
    use FiltersResponsesByOwnership;

    /**
     * The name of the module
     * @var string
     */
    public string $moduleName = 'withdrawal';
    /**
     * Constructor
     */
    public function __construct(Withdrawal $model)
    {
        parent::__construct($model);
    }

    /**
     * Get default relationships for the withdrawal model
     */
    protected function getDefaultRelationships(): array
    {
        return ['user', 'withdrawable', 'currency'];
    }

    /**
     * Get withdrawals with pagination and filters
     */
    public function getWithdrawals(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query(); // Already has ownership filtering applied

        // Apply additional business filters
        $this->applyBusinessFilters($query, $filters);

        return $this->withRelationships($query, $this->getDefaultRelationships())->paginate($perPage);
    }

}

