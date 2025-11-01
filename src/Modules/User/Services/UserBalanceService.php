<?php

namespace App\Modules\User\Services;

use App\Core\Services\BaseService;
use App\Modules\User\Contracts\UserBalanceServiceInterface;
use App\Modules\User\Database\Models\User;
use App\Modules\User\Enums\UserBalanceEnum;
use App\Modules\User\Repositories\UserRepository;

class UserBalanceService extends BaseService implements UserBalanceServiceInterface
{
    protected string $serviceName = 'UserBalanceService';

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        parent::__construct($userRepository);
    }

    /** @var UserRepository */
    private UserRepository $userRepository;

    /**
     * Check if user has sufficient balance for a transaction
     * @param array $data Expected keys: user_id, amount, type, [recipient_id for transfers]
     * @return bool
     */
    public function checkBalance(array $data = []): bool
    {
        try {
            // Step 1: Validate input data structure
            if (!$this->validateBalanceDataInput($data)) {
                $this->log('Balance check failed: invalid input data', ['data' => $data]);
                return false;
            }

            // Step 2: Validate balance based on transaction type
            return $this->validateBalanceData($data);
        } catch (\Exception $e) {
            $this->log('Balance check error', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return false;
        }
    }

    /**
     * Validate input data structure for balance checking
     * @param array $data
     * @return bool
     */
    protected function validateBalanceDataInput(array $data): bool
    {
        // Required fields for all types
        if (!isset($data['user_id']) || !isset($data['amount']) || !isset($data['type'])) {
            return false;
        }

        // Validate user_id is positive integer
        if (!is_numeric($data['user_id']) || $data['user_id'] <= 0) {
            return false;
        }

        // Validate amount is positive numeric
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            return false;
        }

        // Validate type is allowed
        $allowedTypes = array_column(UserBalanceEnum::cases(), 'value');
        if (!in_array($data['type'], $allowedTypes)) {
            return false;
        }

        // Validate recipient_id for transfers
        if (
            $data['type'] === UserBalanceEnum::Transfer->value
            && (!isset($data['recipient_id'])
                || !is_numeric($data['recipient_id'])
                || $data['recipient_id'] <= 0)
        ) {
            return false;
        }

        return true;
    }

    /**
     * Validate balance data based on transaction type
     * @param array $data
     * @return bool
     * @throws \App\Core\Exceptions\ServiceException
     */
    protected function validateBalanceData(array $data): bool
    {
        $userId = (int) $data['user_id'];
        $amount = (float) $data['amount'];

        // Fetch user with fresh data to avoid stale reads
        $user = $this->userRepository->find($userId);

        if (!$user) {
            $this->log('Balance check failed: user not found', ['user_id' => $userId]);
            return false;
        }

        // Route to specific validation based on type
        return match ($data['type']) {
            UserBalanceEnum::Commission->value => $this->validateCommissionBalanceData($user, $amount, $data),
            UserBalanceEnum::Withdraw->value => $this->validateWithdrawBalanceData($user, $amount, $data),
            UserBalanceEnum::Deposit->value => $this->validateDepositBalanceData($user, $amount, $data),
            UserBalanceEnum::Transfer->value => $this->validateTransferBalanceData($user, $amount, $data),
            UserBalanceEnum::Payment->value => $this->validatePaymentBalanceData($user, $amount, $data),
            default => false,
        };
    }

    /**
     * Validate commission balance (usually credits, but can validate constraints)
     * @param User $user
     * @param float $amount
     * @param array $data
     * @return bool
     */
    protected function validateCommissionBalanceData(User $user, float $amount, array $data): bool
    {
        // Commission is typically a credit operation, so always valid
        // But you might want to validate against maximum limits or negative amounts
        if ($amount <= 0) {
            $this->log('Commission validation failed: invalid amount', [
                'user_id' => $user->id,
                'amount' => $amount
            ]);
            return false;
        }

        // Optional: Check if user can receive commission (e.g., account must be active)
        if (!$user->is_active) {
            $this->log('Commission validation failed: user inactive', ['user_id' => $user->id]);
            return false;
        }

        return true;
    }

    /**
     * Validate withdrawal balance - checks available_balance
     * @param User $user
     * @param float $amount
     * @param array $data
     * @return bool
     */
    private function validateWithdrawBalanceData(User $user, float $amount, array $data): bool
    {
        // Withdrawals must check available_balance
        $availableBalance = (float) ($user->available_balance ?? 0);

        if ($availableBalance < $amount) {
            $this->log('Withdrawal validation failed: insufficient balance', [
                'user_id' => $user->id,
                'requested' => $amount,
                'available' => $availableBalance
            ]);
            return false;
        }

        // Optional: Check minimum withdrawal amount (e.g., from config)
        $minWithdrawal = config('user.min_withdrawal', 0);
        if ($amount < $minWithdrawal) {
            $this->log('Withdrawal validation failed: below minimum', [
                'user_id' => $user->id,
                'amount' => $amount,
                'minimum' => $minWithdrawal
            ]);
            return false;
        }

        // Optional: Ensure user account is active
        if (!$user->is_active) {
            $this->log('Withdrawal validation failed: user inactive', ['user_id' => $user->id]);
            return false;
        }

        return true;
    }

    /**
     * Validate deposit balance (usually always valid, but can check limits)
     * @param User $user
     * @param float $amount
     * @param array $data
     * @return bool
     */
    private function validateDepositBalanceData(User $user, float $amount, array $data): bool
    {
        // Deposits are typically credits, so usually valid
        // Validate amount constraints
        if ($amount <= 0) {
            $this->log('Deposit validation failed: invalid amount', [
                'user_id' => $user->id,
                'amount' => $amount
            ]);
            return false;
        }

        // Optional: Check maximum deposit limit
        $maxDeposit = config('user.max_deposit', null);
        if ($maxDeposit !== null && $amount > $maxDeposit) {
            $this->log('Deposit validation failed: exceeds maximum', [
                'user_id' => $user->id,
                'amount' => $amount,
                'maximum' => $maxDeposit
            ]);
            return false;
        }

        return true;
    }

    /**
     * Validate transfer balance - checks sender's available_balance
     * @param User $user (sender)
     * @param float $amount
     * @param array $data
     * @return bool
     */
    private function validateTransferBalanceData(User $user, float $amount, array $data): bool
    {
        // Validate sender has sufficient available balance
        $availableBalance = (float) ($user->available_balance ?? 0);

        if ($availableBalance < $amount) {
            $this->log('Transfer validation failed: insufficient balance', [
                'user_id' => $user->id,
                'requested' => $amount,
                'available' => $availableBalance
            ]);
            return false;
        }

        // Validate recipient exists and is valid
        $recipientId = (int) ($data['recipient_id'] ?? 0);
        if ($recipientId <= 0) {
            return false;
        }

        $recipient = $this->userRepository->find($recipientId);
        if (!$recipient) {
            $this->log('Transfer validation failed: recipient not found', [
                'sender_id' => $user->id,
                'recipient_id' => $recipientId
            ]);
            return false;
        }

        // Validate recipient is not the same as sender
        if ($user->id === $recipient->id) {
            $this->log('Transfer validation failed: cannot transfer to self', [
                'user_id' => $user->id
            ]);
            return false;
        }

        // Optional: Check if recipient account is active
        if (!$recipient->is_active) {
            $this->log('Transfer validation failed: recipient inactive', [
                'recipient_id' => $recipientId
            ]);
            return false;
        }

        return true;
    }

    /**
     * Validate payment balance - checks available_balance
     * @param User $user
     * @param float $amount
     * @param array $data
     * @return bool
     */
    private function validatePaymentBalanceData(User $user, float $amount, array $data): bool
    {
        // Payments must check available_balance
        $availableBalance = (float) ($user->available_balance ?? 0);

        if ($availableBalance < $amount) {
            $this->log('Payment validation failed: insufficient balance', [
                'user_id' => $user->id,
                'requested' => $amount,
                'available' => $availableBalance
            ]);
            return false;
        }

        // Optional: Check minimum payment amount
        $minPayment = config('user.min_payment', 0);
        if ($amount < $minPayment) {
            $this->log('Payment validation failed: below minimum', [
                'user_id' => $user->id,
                'amount' => $amount,
                'minimum' => $minPayment
            ]);
            return false;
        }

        // Optional: Ensure user account is active
        if (!$user->is_active) {
            $this->log('Payment validation failed: user inactive', ['user_id' => $user->id]);
            return false;
        }

        return true;
    }
}
