<?php

namespace App\Modules\User\Services;

use App\Core\Http\ServiceResponse;
use App\Core\Services\BaseService;
use App\Modules\User\Contracts\UserServiceInterface;
use App\Modules\User\Database\Models\User;
use App\Modules\User\Repositories\UserRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

/**
 * User Service
 */
class UserService extends BaseService implements UserServiceInterface
{
    protected string $serviceName = 'UserService';

    public function __construct(
        private UserRepository $userRepository
    ) {
        parent::__construct();
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
            // Validate input data
            $validated = $this->validateUserData($data);

            // Hash password if provided
            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }

            $user = $this->userRepository->create($validated);

            $this->log('User created successfully', ['user_id' => $user->id]);

            return ServiceResponse::success($user, 'User created successfully', Response::HTTP_CREATED);
        }, 'create user');
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

            $this->log('User found by ID', ['user_id' => $id]);

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

            $this->log('User found by UUID', ['uuid' => $uuid]);

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

            $this->log('User found by email', ['email' => $email]);

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
            $validated = $this->validateUserData($data, $id);

            // Hash password if provided
            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }

            $updated = $this->userRepository->update($id, $validated);

            if (!$updated) {
                throw new \App\Core\Exceptions\ServiceException('Failed to update user');
            }

            $this->log('User updated successfully', ['user_id' => $id]);

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

            $this->log('User deleted successfully', ['user_id' => $id]);

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

            $this->log('Users retrieved', [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $users->total(),
                'filters' => $filters
            ]);

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

            $this->log('Users searched', [
                'search' => $search,
                'page' => $page,
                'per_page' => $perPage,
                'total' => $users->total()
            ]);

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

            $this->log('User statistics retrieved', $stats);

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
            'name' => 'required|string|max:255',
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
}
