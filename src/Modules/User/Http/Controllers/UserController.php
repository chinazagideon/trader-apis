<?php

namespace App\Modules\User\Http\Controllers;

use App\Core\Controllers\BaseController;
use App\Modules\User\Contracts\UserServiceInterface;
use App\Modules\User\Database\Models\User;
use App\Modules\User\Http\Requests\CreateUserRequest;
use App\Modules\User\Http\Requests\UpdateUserRequest;
use App\Modules\User\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends BaseController
{
    public function __construct(
        private UserServiceInterface $userService
    ) {}

    /**
     * Display a listing of users
     */
    public function index(Request $request): JsonResponse
    {
        // Authorize the action using policy
        Gate::authorize('viewAny', User::class);

        $filters = $this->getSearchParams($request);
        $pagination = $this->getPaginationParams($request);

        $response = $this->userService->getAll(
            $filters,
            $pagination['page'],
            $pagination['per_page']
        );

        // Transform the data using UserResource if successful
        if ($response->isSuccess()) {
            $response->setData(UserResource::collection($response->getData()));
        }

        return $this->handleServiceResponse($response);
    }

    /**
     * Store a newly created user
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        // Authorization is handled in CreateUserRequest via authorize() method
        // Additional authorization check here for extra safety
        Gate::authorize('create', User::class);

        $response = $this->userService->create($request->validated());

        // Transform the data using UserResource if successful
        if ($response->isSuccess()) {
            $response->setData(new UserResource($response->getData()));
        }

        return $this->handleServiceResponse($response);
    }

    /**
     * Display the specified user
     */
    public function show(int $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        // Check if user was found
        if (!$user->isSuccess() || !$user->getData()) {
            return $this->notFoundResponse('User not found');
        }

        $userModel = $user->getData();

        // Authorize viewing this specific user
        Gate::authorize('view', $userModel);

        // Transform the data using UserResource if successful
        if ($user->isSuccess()) {
            $user->setData(new UserResource($userModel));
        }

        return $this->handleServiceResponse($user);
    }

    /**
     * Display user by UUID
     */
    public function showByUuid(string $uuid): JsonResponse
    {
        $response = $this->userService->findByUuid($uuid);

        // Check if user was found
        if (!$response->isSuccess() || !$response->getData()) {
            return $this->notFoundResponse('User not found');
        }

        $userModel = $response->getData();

        // Authorize viewing this specific user
        Gate::authorize('view', $userModel);

        // Transform the data using UserResource if successful
        if ($response->isSuccess()) {
            $response->setData(new UserResource($userModel));
        }

        return $this->handleServiceResponse($response);
    }

    /**
     * Update the specified user
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $response = $this->userService->update($id, $request->validated());

        // Transform the data using UserResource if successful
        if ($response->isSuccess()) {
            $response->setData(new UserResource($response->getData()));
        }

        return $this->handleServiceResponse($response);
    }

    /**
     * Remove the specified user
     */
    public function destroy(int $id): JsonResponse
    {
        // Get the user first to authorize
        $userResponse = $this->userService->findById($id);

        if (!$userResponse->isSuccess() || !$userResponse->getData()) {
            return $this->notFoundResponse('User not found');
        }

        $userModel = $userResponse->getData();

        // Authorize deleting this specific user
        Gate::authorize('delete', $userModel);

        $response = $this->userService->delete($id);

        return $this->handleServiceResponse($response);
    }

    /**
     * Search users
     */
    public function search(Request $request): JsonResponse
    {
        // Authorize search action using policy
        $user = auth()->user();
        if (!$user || !Gate::forUser($user)->allows('search', User::class)) {
            return $this->forbiddenResponse('You do not have permission to search users');
        }

        $search = $request->get('q', '');
        $pagination = $this->getPaginationParams($request);

        if (empty($search)) {
            return $this->errorResponse('Search query is required');
        }

        $response = $this->userService->searchUsers(
            $search,
            $pagination['page'],
            $pagination['per_page']
        );

        // Transform the data using UserResource if successful
        if ($response->isSuccess()) {
            $response->setData(UserResource::collection($response->getData()));
        }

        return $this->handleServiceResponse($response);
    }

    /**
     * Get user statistics
     */
    public function stats(): JsonResponse
    {
        // Authorize statistics action using policy
        $user = auth()->user();
        if (!$user || !Gate::forUser($user)->allows('viewStatistics', User::class)) {
            return $this->forbiddenResponse('You do not have permission to view user statistics');
        }

        $response = $this->userService->getUserStats();

        return $this->handleServiceResponse($response);
    }
}
