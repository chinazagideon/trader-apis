<?php

namespace App\Modules\Notification\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Notification\Services\NotificationService;
use App\Modules\Notification\Http\Resources\NotificationResource;
use App\Modules\Notification\Http\Requests\NotificationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends CrudController
{
    public function __construct(
        private NotificationService $notificationService
    ) {
        parent::__construct($notificationService);
    }

    /**
     * Get notifications for an entity
     *
     * @param NotificationRequest $request
     * @return JsonResponse
     */
    public function index(NotificationRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $entityType = $validatedData['entity_type'];
        $entityId = $validatedData['entity_id'];
        $perPage = $validatedData['per_page'] ?? 15;

        // Resolve entity type to full class name
        $resolvedEntityType = $this->notificationService->resolveEntityType($entityType);

        $response = $this->notificationService->getForEntity($entityType, $entityId, $perPage);

        if ($response->isSuccess()) {
            // Transform data using NotificationResource if successful
            $response->setData(NotificationResource::collection($response->getData()));
            return $this->handleServiceResponse($response);
        }

        return $this->errorResponse($response->getMessage(), null, $response->getHttpStatusCode());
    }

    /**
     * Get unread notifications for an entity
     *
     * @param NotificationRequest $request
     * @return JsonResponse
     */
    public function unread(NotificationRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $entityType = $validatedData['entity_type'];
        $entityId = $validatedData['entity_id'];
        $perPage = $validatedData['per_page'] ?? 15;

        // Resolve entity type to full class name
        $resolvedEntityType = $this->notificationService->resolveEntityType($entityType);

        $response = $this->notificationService->getUnreadForEntity($entityType, $entityId, $perPage);

        if ($response->isSuccess()) {
            return $this->handleServiceResponse($response);
        }

        return $this->errorResponse($response->getMessage(), null, $response->getHttpStatusCode());
    }

    /**
     * Get unread count
     *
     * @param NotificationRequest $request
     * @return JsonResponse
     */
    public function unreadCount(NotificationRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $entityType = $validatedData['entity_type'];
        $entityId = $validatedData['entity_id'];

        // Resolve entity type to full class name
        $resolvedEntityType = $this->notificationService->resolveEntityType($entityType);

        $response = $this->notificationService->getUnreadCount($entityType, $entityId);

        if ($response->isSuccess()) {
            return $this->handleServiceResponse($response);
        }

        return $this->errorResponse($response->getMessage(), null, $response->getHttpStatusCode());
    }

    /**
     * Mark notification as read
     *
     * @param string $id
     * @return JsonResponse
     */
    public function markAsRead(string $id): JsonResponse
    {
        $response = $this->notificationService->markAsRead($id);

        if ($response->isSuccess()) {
            return $this->handleServiceResponse($response);
        }

        return $this->errorResponse($response->getMessage(), null, $response->getHttpStatusCode());
    }

    /**
     * Mark all as read for entity
     *
     * @param NotificationRequest $request
     * @return JsonResponse
     */
    public function markAllAsRead(NotificationRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $entityType = $validatedData['entity_type'];
        $entityId = $validatedData['entity_id'];

        // Resolve entity type to full class name
        $resolvedEntityType = $this->notificationService->resolveEntityType($entityType);

        $response = $this->notificationService->markAllAsReadForEntity($entityType, $entityId);

        if ($response->isSuccess()) {
            return $this->handleServiceResponse($response);
        }

        return $this->errorResponse($response->getMessage(), null, $response->getHttpStatusCode());
    }

    /**
     * Retry failed notification
     *
     * @param string $id
     * @return JsonResponse
     */
    public function retry(string $id): JsonResponse
    {
        $response = $this->notificationService->retry($id);

        if ($response->isSuccess()) {
            return $this->handleServiceResponse($response);
        }

        return $this->errorResponse($response->getMessage(), null, $response->getHttpStatusCode());
    }
}
