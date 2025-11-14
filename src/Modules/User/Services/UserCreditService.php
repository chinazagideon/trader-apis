<?php

namespace App\Modules\User\Services;

use App\Core\Services\BaseService;
use App\Modules\User\Contracts\UserCreditServiceInterface;
use App\Modules\User\Database\Models\User;
use App\Modules\User\Enums\UserBalanceEnum;
use App\Modules\User\Enums\UserPaymentTypes;
use App\Modules\User\Repositories\UserRepository;
use App\Core\Exceptions\AppException;

class UserCreditService extends BaseService implements UserCreditServiceInterface
{
    protected string $serviceName = 'UserCreditService';

    public function __construct(
        private UserRepository $userRepository
    ) {
        parent::__construct($userRepository);
    }

    /**
     * Add credit to user
     * @param array $data
     * @return bool
     */
    public function credit(array $data = []): bool
    {
        $this->log('EXECUTING credit user service', $data);


        $creditType = $data['type'] ?? null;
        $amount = $data['amount'] ?? 0;
        $userId = $data['user_id'] ?? 0;

        //validate credit type
        if (!in_array($creditType, $this->allowedCreditTypes())) {
            throw new AppException('Invalid credit type');
        }
        //credit value to user total balance
        $this->updateTotalBalance(['user_id' => $userId, 'amount' => $amount]);

        //if allow auto credit, update available balance
        if(config('User.allow_auto_credit', false)) {
            $this->updateAvailableBalance(['user_id' => $userId, 'amount' => $amount]);
        }
          // //credit value to user commission balance
        if ($creditType === UserBalanceEnum::Commission->value) {
            $this->updateCommissionBalance(['user_id' => $userId, 'amount' => $amount]);
        }

        return true;
    }

    /**
     * update user balance
     *
     * @param array $data
     * @return void
     */
    public function updateTotalBalance(array $data = []): void
    {
        $amount = $data['amount'];
        $userId = $data['user_id'];
        $user = $this->userRepository->find($userId);
        $user->total_balance += $amount;
        $user->saveOrFail();

    }

    /**
     * update available balance
     *
     * @param array $data
     * @return void
     */
    public function updateAvailableBalance(array $data = []): void
    {
        $amount = $data['amount'];
        $userId = $data['user_id'];

        $user = $this->userRepository->find($userId);

        $user->available_balance += $amount;
        $user->saveOrFail();

    }

    /**
     * update commission balance
     *
     * @param array $data
     * @return void
     */
    public function updateCommissionBalance(array $data = []): void
    {
        $amount = $data['amount'];
        $userId = $data['user_id'];

        $user = $this->userRepository->find($userId);
        $user->total_commission += $amount;

        $user->saveOrFail();
    }

    /**
     * Get the allowed credit types
     * @return array
     */
    public function allowedCreditTypes(): array
    {
        return [
            UserPaymentTypes::Funding->value,
            UserBalanceEnum::Commission->value,
        ];
    }
}
