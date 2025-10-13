<?php

namespace App\Modules\Transaction\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Transaction\Database\Models\Transaction;
use App\Modules\Transaction\Repositories\TransactionRepository;

class TransactionService extends BaseService
{
    public function __construct(
        private TransactionRepository $transactionRepository
    ) {
        parent::__construct($transactionRepository);
    }

    protected string $serviceName = 'TransactionService';

    public function unsetCategoryId(array $data): array
    {
        unset($data['transaction_category_id']);
        return $data;
    }

    public function afterStore(array $data): ?Transaction
    {
        return $this->transactionRepository->find($data['id']) ?? null;
    }
}
