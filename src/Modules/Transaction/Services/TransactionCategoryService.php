<?php

namespace App\Modules\Transaction\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Transaction\Repositories\TransactionCategoryRepository;

class TransactionCategoryService extends BaseService
{
    public function __construct(
        private TransactionCategoryRepository $transactionCategoryRepository
    ) {
        parent::__construct($transactionCategoryRepository);
    }

    protected string $serviceName = 'TransactionCategoryService';

    /**
     * Override index method to load relationships
     */
    public function index(array $filters = [], int $perPage = 15): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($filters, $perPage) {
            $results = $this->repository->all();

            // Load relationships for each result
            $results->load(['category', 'transaction']);

            return ServiceResponse::success($results, 'Transaction categories retrieved successfully.');
        }, 'index');
    }

    /**
     * Override show method to load relationships
     */
    public function show(int $id): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($id) {
            $result = $this->repository->find($id);

            if (!$result) {
                throw new \App\Core\Exceptions\NotFoundException('Transaction category not found');
            }

            // Load relationships
            $result->load(['category', 'transaction']);

            return ServiceResponse::success($result, 'Transaction category retrieved successfully.');
        }, 'show');
    }

    /**
     * Apply filters to the query
     */
    private function applyFilters($query, array $filters)
    {
        if (isset($filters['transaction_id'])) {
            $query->where('transaction_id', $filters['transaction_id']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query;
    }

    public function beforeStore(array $data): array
    {
        $this->logger->logOperation(
            'TransactionCategoryService',
            'beforeStore',
            'success',
            "Transaction category created successfully",
            [
                'transaction_id' => $data['transaction_id'] ?? null,
                'category_id' => $data['category_id'] ?? null,
            ]
        );

        return $data;
    }
}
