<?php

namespace App\Modules\User\Http\Controllers;

use App\Core\Controllers\BaseController;
use App\Modules\User\Contracts\UserServiceInterface;
use App\Modules\User\Http\Requests\CreateUserRequest;
use App\Modules\User\Http\Requests\UpdateUserRequest;
use App\Modules\User\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $response = $this->userService->findById($id);

        // Transform the data using UserResource if successful
        if ($response->isSuccess()) {
            $response->setData(new UserResource($response->getData()));
        }

        return $this->handleServiceResponse($response);
    }

    /**
     * Display user by UUID
     */
    public function showByUuid(string $uuid): JsonResponse
    {
        $response = $this->userService->findByUuid($uuid);

        // Transform the data using UserResource if successful
        if ($response->isSuccess()) {
            $response->setData(new UserResource($response->getData()));
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
        $response = $this->userService->delete($id);

        return $this->handleServiceResponse($response);
    }

    /**
     * Search users
     */
    public function search(Request $request): JsonResponse
    {
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
        $response = $this->userService->getUserStats();

        return $this->handleServiceResponse($response);
    }
}
