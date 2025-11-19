<?php

namespace App\Modules\User\Services;

use App\Core\Http\ServiceResponse;
use App\Core\Services\BaseService;
use App\Modules\User\Contracts\UserServiceInterface;
use App\Modules\User\Contracts\UserBalanceServiceInterface;
use App\Modules\User\Repositories\UserRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use App\Modules\User\Contracts\UserCreditServiceInterface;
use App\Modules\Role\Contracts\RoleServiceContract;
use App\Modules\User\Events\UserWasCreatedEvent;
use App\Modules\User\Enums\UserPaymentTypes;
use App\Modules\User\Contracts\UserDebitServiceInterface;
use App\Modules\Payment\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Core\Services\EventDispatcher;
use App\Modules\User\Facade\UserModelFacade;
use App\Modules\Market\Contracts\MarketFiatServiceInterface;
use App\Modules\Currency\Contracts\CurrencyServiceContract;
 /**
 * User Service
 */
class UserService extends BaseService implements UserServiceInterface
{
    protected string $serviceName = 'UserService';

    public function __construct(
        private UserRepository $userRepository,
        private UserBalanceServiceInterface $userBalanceService,
        private UserCreditServiceInterface $userCreditService,
        private RoleServiceContract $roleService,
        private EventDispatcher $eventDispatcher,
        private UserDebitServiceInterface $userDebitService,
        private MarketFiatServiceInterface $marketFiatService,
        private CurrencyServiceContract $currencyService
    ) {
        parent::__construct($userRepository);
    }

    /**
     * Create a new user
     * @param array $data
     * @return ServiceResponse
     * @throws \App\Core\Exceptions\ServiceException
     */
    public function create(array $data): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($data) {

            $data = $this->prepareData($data);
            // Hash password if provided
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            // $user = parent::create($validated);
            $user = $this->userRepository->create($data);

            // Dispatch UserWasCreatedEvent to trigger notifications
            $this->eventDispatcher->dispatch(new UserWasCreatedEvent($user), 'user_was_created');

            Log::info('UserWasCreatedEvent dispatched', [
                'user' => $user,
            ]);

            return ServiceResponse::success($user, 'User created successfully', Response::HTTP_CREATED);
        }, 'create user');
    }

    /**
     * Prepare data for user creation
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $role_id = $this->assignDefaultRole();
        $data['role_id'] = $role_id;
        return $data;
    }

    /**
     * Assign default role to user
     * @return int
     */
    private function assignDefaultRole(): int
    {
        $roleResponse = $this->roleService->getUserRole();

        if (!$roleResponse->isSuccess()) {
            throw new \App\Core\Exceptions\ServiceException('Failed to get default role');
        }
        return $roleResponse->getData()->id;
    }

    /**
     * Find user by ID
     * @param int $id
     * @return ServiceResponse
     * @throws \App\Core\Exceptions\ServiceException
     */
    public function findById(int $id): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($id) {
            $user = $this->userRepository->find($id);

            $this->ensureResourceExists($user, 'User');

            return ServiceResponse::success($user, 'User retrieved successfully');
        }, 'find user by ID');
    }

    /**
     * Find user by UUID
     * @param string $uuid
     * @return ServiceResponse
     * @throws \App\Core\Exceptions\ServiceException
     */
    public function findByUuid(string $uuid): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($uuid) {
            $user = $this->userRepository->findBy('uuid', $uuid);

            $this->ensureResourceExists($user, 'User');

            return ServiceResponse::success($user, 'User retrieved successfully');
        }, 'find user by UUID');
    }

    /**
     * Find user by email
     * @param string $email
     * @return ServiceResponse
     * @throws \App\Core\Exceptions\ServiceException
     */
    public function findByEmail(string $email): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($email) {
            $user = $this->userRepository->findByEmail($email);

            $this->ensureResourceExists($user, 'User');

            return ServiceResponse::success($user, 'User retrieved successfully');
        }, 'find user by email');
    }

    /**
     * Update user
     * @param int $id
     * @param array $data
     * @return ServiceResponse
     * @throws \App\Core\Exceptions\ServiceException
     */
    public function update(int $id, array $data): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($id, $data) {
            $user = $this->userRepository->find($id);
            $this->ensureResourceExists($user, 'User');

            // Validate input data
            // $validated = $this->validateUserData($data, $id);

            // Hash password if provided
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $updated = $this->userRepository->update($id, $data);

            if (!$updated) {
                throw new \App\Core\Exceptions\ServiceException('Failed to update user');
            }

            // Return the updated user
            $updatedUser = $this->userRepository->find($id);
            return ServiceResponse::success($updatedUser, 'User updated successfully');
        }, 'update user');
    }

    /**
     * Delete user
     * @param int $id
     * @return ServiceResponse
     * @throws \App\Core\Exceptions\ServiceException
     */
    public function delete(int $id): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($id) {
            $user = $this->userRepository->find($id);
            $this->ensureResourceExists($user, 'User');

            $deleted = $this->userRepository->delete($id);

            if (!$deleted) {
                throw new \App\Core\Exceptions\ServiceException('Failed to delete user');
            }

            return ServiceResponse::success(null, 'User deleted successfully');
        }, 'delete user');
    }

    /**
     * Get all users
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return ServiceResponse
     * @throws \App\Core\Exceptions\ServiceException
     */
    public function getAll(array $filters = [], int $page = 1, int $perPage = 15): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($filters, $page, $perPage) {
            $users = $this->userRepository->getUsersWithFilters($filters, $perPage);

            $pagination = [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ];

            return ServiceResponse::success($users->items(), 'Users retrieved successfully', Response::HTTP_OK, $pagination);
        }, 'get all users');
    }

    /**
     * Search users
     * @param string $search
     * @param int $page
     * @param int $perPage
     * @return ServiceResponse
     * @throws \App\Core\Exceptions\ServiceException
     */
    public function searchUsers(string $search, int $page = 1, int $perPage = 15): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($search, $page, $perPage) {
            $users = $this->userRepository->searchUsers($search, $perPage);

            $pagination = [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ];

            return ServiceResponse::success($users->items(), 'Search results retrieved successfully', Response::HTTP_OK, $pagination);
        }, 'search users');
    }

    /**
     * Get user statistics
     * @return ServiceResponse
     * @throws \App\Core\Exceptions\ServiceException
     */
    public function getUserStats(): ServiceResponse
    {
        return $this->executeServiceOperation(function () {
            $stats = $this->userRepository->getUserStats();
            return ServiceResponse::success($stats, 'User statistics retrieved successfully');
        }, 'get user statistics');
    }

    /**
     * Validate user data
     * @param array $data
     * @param int|null $userId
     * @return array
     * @throws \App\Core\Exceptions\ValidationException
     */
    private function validateUserData(array $data, ?int $userId = null): array
    {
        $rules = [
            'name' => 'sometimes|nullable|string|max:255',
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'referral_code' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:users,email' . ($userId ? ',' . $userId : ''),
            'password' => 'sometimes|string|min:8',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'is_active' => 'sometimes|boolean',
        ];

        return $this->validateData($data, $rules);
    }

    /**
     * validate user balance
     *
     * @param array $data
     * @return boolean
     */
    public function checkBalance(array $data = []): bool
    {
        return $this->userBalanceService->checkBalance($data);
    }

    /**
     * Add funding to user
     * @param array $data
     * @return ServiceResponse
     */
    public function addFundingToUser(array $data = []): void
    {
        $this->userCreditService->credit($data);
    }

    /**
     * Update available balance
     * @param array $data
     * @return void
     */
    public function updateAvailableBalance(array $data = []): void
    {
        $this->userCreditService->updateAvailableBalance($data);
    }

    /**
     * Update commission balance
     * @param array $data
     * @return void
     */
    public function updateCommissionBalance(array $data = []): void
    {
        $this->userCreditService->updateCommissionBalance($data);
    }

    /**
     * Prepare balance data
     * @param array $data
     * @return array
     */
    public function prepareBalanceData(array $data = []): array
    {
        $user = $this->userRepository->find($data['user_id']);
        $this->ensureResourceExists($user, 'User');

        return [
            'user_id' => $user->id,
            'amount' => $data['amount'],
        ];
    }

    /**
     * Credit user
     * @param array $data
     * @return ServiceResponse
     */
    public function creditAvailableBalance(array $data = []): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($data) {
            $this->updateAvailableBalance($data);
            return ServiceResponse::success(null, 'Credit available balance successful');
        }, 'credit available balance');
    }

    /**
     * Credit commission balance
     * @param array $data
     * @return ServiceResponse
     */
    public function creditCommissionBalance(array $data = []): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($data) {
            $this->updateCommissionBalance($data);
            return ServiceResponse::success(null, 'Credit commission balance successful');
        }, 'credit commission balance');
    }

    /**
     * Resolve payment
     * @param array $data
     * @return void
     */
    public function resolvePayment(array $data = []): void
    {
        $status = $data['status'];
        $type = $data['type'];


        Log::info('Resolve payment', [
            'data' => $data,
            'status' => $status,
            'type' => $type,
        ]);

        //if status is completed, resolve payment
        if ($status === PaymentStatusEnum::COMPLETED->value) {
            //funding
            if ($type === UserPaymentTypes::Funding->value) {
                Log::info('Update available balance for funding', [
                    'data' => $data,
                ]);
                $this->updateAvailableBalanceForFunding($data);
            }
            //withdraw fund
            if ($type === UserPaymentTypes::Withdrawal->value) {
                Log::info('Update available balance for withdrawal', [
                    'data' => $data,
                ]);
                $this->updateAvailableBalanceForWithdrawal($data);
            }
        }
    }

    /**
     * Resolve funding payment
     * @param array $data
     * @return void
     */
    private function updateAvailableBalanceForFunding(array $data = []): void
    {
        $preparedData = $this->prepareCreditData($data);
        Log::info('Prepared data for funding', [
            'preparedData' => $preparedData,
        ]);
        $this->userCreditService->updateAvailableBalance($preparedData);
    }

    /**
     * Withdraw fund
     * @param array $data
     * @return void
     */
    private function updateAvailableBalanceForWithdrawal(array $data = []): void
    {
        $preparedData = $this->prepareDebitData($data);
        Log::info('Prepared data for withdrawal', [
            'preparedData' => $preparedData,
        ]);
        $this->userDebitService->debitAvailableBalance($preparedData);
    }

    /**
     * Prepare debit data
     * @param array $data
     * @return array
     */
    private function prepareDebitData(array $data = []): array
    {

        return [
            'user_id' => $data['user_id'],
            'amount' => $data['amount'],
            'type' => $data['type'],
        ];
    }

    /**
     * Prepare credit data
     * @param array $data
     * @return array
     */
    private function prepareCreditData(array $data = []): array
    {

        $isFiatCurrency = $this->isFiatCurrency($data['currency_id']);
        Log::info('Is fiat currency', [
            'currency_id' => $data['currency_id'],
            'is_fiat_currency' => $isFiatCurrency,
        ]);
        if ($isFiatCurrency) {
            $amount = $data['amount'];
        } else {
            $conversionResponse = $this->convertFiatToCrypto($data);
            if (!$conversionResponse->isSuccess()) {
                throw new \App\Core\Exceptions\ServiceException($conversionResponse->getMessage());
            }
            $amount = $conversionResponse->getData();
        }
        return [
            'user_id' => $data['user_id'],
            'amount' => $amount,
            'type' => $data['type'],
            'currency_id' => $data['currency_id'],
        ];
    }

    /**
     * Validate user active
     * @param array $data
     * @return void
     */
    private function validateUserActive(int $userId): void
    {

    }

    /**
     * Change password
     * @param array $data
     * @return ServiceResponse
     */
    public function changePassword(array $data = []): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($data) {

            return ServiceResponse::success(null, 'Password changed successfully');
        }, 'change password');
    }

    /**
     * Convert fiat to crypto
     * @param array $data
     * @return ServiceResponse
     */
    public function convertFiatToCrypto(array $data = []): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($data) {
            Log::info('Convert fiat to crypto', [
                'data' => $data,
                'default_fiat_currency_id' => $this->getDefaultFiatCurrencyId(),
            ]);
            $preparedData = [
                'amount' => $data['amount'],
                'currency_id' => $data['currency_id'],
                'fiat_currency_id' => $this->getDefaultFiatCurrencyId(),
            ];
            $response = $this->marketFiatService->fiatConverter($preparedData);
            Log::info('Response from fiat converter', [
                'response' => $response,
                'preparedData' => $preparedData,
            ]);
            if (!$response->isSuccess()) {
                throw new \App\Core\Exceptions\ServiceException($response->getMessage());
            }
            return ServiceResponse::success($response->getData()->fiat_amount, 'Fiat converted to crypto successfully');
        }, 'convert fiat to crypto');
    }

    /**
     * Get default fiat currency id
     * @param array $data
     * @return int
     */
    private function getDefaultFiatCurrencyId(): int
    {
        $currency = $this->currencyService->getDefaultCurrency();
        if (!$currency->isSuccess()) {
            throw new \App\Core\Exceptions\ServiceException($currency->getMessage());
        }
        return $currency->getData()->id;
    }

    /**
     * Check if currency is fiat
     * @param int $id
     * @return bool
     */
    private function isFiatCurrency(int $id): bool
    {
        $is_fiat = $this->currencyService->isFiatCurrency($id);
        Log::info('Is fiat currency', [
            'id' => $id,
            'is_fiat' => $is_fiat,
            'default_fiat_currency_id' => $this->getDefaultFiatCurrencyId(),
        ]);
        if ($is_fiat && $this->getDefaultFiatCurrencyId() !== $id) {
            throw new \App\Core\Exceptions\ServiceException('Invalid currency or fiat currency combination');
        }
        return $is_fiat;
    }
}
