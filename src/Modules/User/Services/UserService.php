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
        private RoleServiceContract $roleService
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
}
