<?php

namespace App\Modules\Transaction\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Transaction\Database\Models\Transaction;
use App\Modules\Transaction\Repositories\TransactionRepository;

class TransactionService extends BaseService
{
    /**
     * __construct
     *
     * @param TransactionRepository $transactionRepository
     */
    public function __construct(
        private TransactionRepository $transactionRepository
    ) {
        parent::__construct($transactionRepository);
    }

    /**
     * service name
     *
     * @var string
     */
    protected string $serviceName = 'TransactionService';

    /**
     * unset category id from data
     *
     * @param array $data
     * @return array
     */
    public function unsetCategoryId(array $data): array
    {
        unset($data['transaction_category_id']);
        return $data;
    }

    /**
     * excute action after store
     *
     * @param array $data
     * @return Transaction|null
     */
    public function afterStore(array $data): ?Transaction
    {
        return $this->transactionRepository->find($data['id']) ?? null;
    }

    /**
     * index
     *
     * @param array $filters
     * @param int $perPage
     * @return ServiceResponse
     */
    public function index(array $filters = [], int $perPage = 15): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($filters, $perPage) {
            $paginator = $this->transactionRepository->getTransactions($filters, $perPage);
            return $this->createPaginatedResponse($paginator, 'Transactions retrieved successfully');
        }, 'index');
    }
}
