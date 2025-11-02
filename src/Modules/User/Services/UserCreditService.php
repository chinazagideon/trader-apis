<?php

namespace App\Modules\User\Services;

use App\Core\Services\BaseService;
use App\Modules\User\Contracts\UserCreditServiceInterface;
use App\Modules\User\Database\Models\User;
use App\Modules\User\Enums\UserBalanceEnum;
use App\Modules\User\Repositories\UserRepository;
use App\Core\Exceptions\ValidationException;
use App\Core\Services\EventDispatcher;
use App\Modules\User\Events\UserBalanceWasUpdated;
use Illuminate\Support\Facades\Log;

class UserCreditService extends BaseService implements UserCreditServiceInterface
{
    protected string $serviceName = 'UserCreditService';

    public function __construct(
        private UserRepository $userRepository,
        private EventDispatcher $eventDispatcher

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
        $creditType = $data['type'] ?? null;
        $amount = $data['amount'] ?? 0;
        $userId = $data['user_id'] ?? 0;

        //credit value to user total balance but NOT available to balance
        $this->updateTotalBalance([
            'user_id' => $userId,
            'amount' => $amount,
        ]);

        //if allow auto credit, update available balance
        if (config('User.allow_auto_credit')) {
            $this->updateAvailableBalance([
                'user_id' => $userId,
                'amount' => $amount,
            ]);
        }

        if($creditType === UserBalanceEnum::Commission->value) {
            $this->updateCommissionBalance([
                'user_id' => $userId,
                'amount' => $amount,
            ]);
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

        Log::info('UserCreditService: before update total balance', [
            'user_id' => $userId,
            'amount' => $amount,
        ]);

        $user = $this->userRepository->find($userId);
        Log::info('UserCreditService: after find user', [
            'user_id' => $userId,
            'user' => $user,
        ]);
        $user->total_balance += $amount;
        Log::info('UserCreditService: after update total balance', [
            'user_id' => $userId,
            'user' => $user,
        ]);
        $user->saveOrFail();

        Log::info('UserCreditService: update total balance', [
            'user_id' => $userId,
            'amount' => $amount,
            'user' => $user,
        ]);

        //emit balance was updated
        $this->eventDispatcher->dispatch(new UserBalanceWasUpdated($user));

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

        Log::info('UserCreditService: update available balance', [
            'user_id' => $userId,
            'amount' => $amount,
            'user' => $user,
        ]);

        //emit balance was updated
        $this->eventDispatcher->dispatch(new UserBalanceWasUpdated($user));

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

        Log::info('UserCreditService: update commission balance', [
            'user_id' => $userId,
            'amount' => $amount,
            'user' => $user,
        ]);

        //emit balance was updated
        $this->eventDispatcher->dispatch(new UserBalanceWasUpdated($user));
    }

}
