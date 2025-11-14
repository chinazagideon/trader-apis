<?php

namespace App\Modules\User\Services;

use App\Core\Services\BaseService;
use App\Modules\User\Contracts\UserDebitServiceInterface;
use App\Modules\User\Repositories\UserRepository;
use App\Modules\User\Enums\UserPaymentTypes;
use App\Core\Exceptions\AppException;

class UserDebitService extends BaseService implements UserDebitServiceInterface
{
    /**
     * The service name
     * @var string
     */
    protected string $serviceName = 'UserDebitService';

    /**
     * The constructor
     * @param UserRepository $userRepository
     */
    public function __construct(
        private UserRepository $userRepository
    ) {
        parent::__construct($userRepository);
    }


    /**
     * Debit
     * @param array $data
     * @return void
     */
    public function debit(array $data = []): void
    {
        $this->log('EXECUTING debit user service', $data);

        $amount = $data['amount'] ?? 0;
        $userId = $data['user_id'] ?? 0;
        $type = $data['type'] ?? null;

        if (!in_array($type, $this->allowedDebitTypes())) {
            throw new AppException('Invalid debit type');
        }

        //debit value from user available balance
        $this->debitAvailableBalance([
            'user_id' => $userId,
            'amount' => $amount,
        ]);
    }

    /**
     * Debit total balance
     * @param array $data
     * @return void
     */
    public function debitTotalBalance(array $data = []): void
    {
        $amount = $data['amount'];
        $userId = $data['user_id'];
        $user = $this->userRepository->find($userId);
        $user->total_balance -= $amount;
        $user->saveOrFail();
    }

    /**
     * Debit available balance
     * @param array $data
     * @return void
     */
    public function debitAvailableBalance(array $data = []): void
    {
        $amount = $data['amount'];
        $userId = $data['user_id'];
        $user = $this->userRepository->find($userId);
        $user->available_balance -= $amount;
        $user->saveOrFail();
    }

    /**
     * Get the allowed debit types
     * @return array
     */
    public function allowedDebitTypes(): array
    {
        return [
            UserPaymentTypes::Withdrawal->value,
        ];
    }
}
