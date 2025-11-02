<?php

namespace App\Modules\Notification\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Notification\Repositories\NotificationRepository;
use App\Modules\Notification\Notifications\BaseNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class NotificationService extends BaseService
{
    protected string $serviceName = 'NotificationService';

    public function __construct(
        private NotificationRepository $notificationRepository,
        private ProviderManager $providerManager
    ) {
        parent::__construct($notificationRepository);
    }

    /**
     * Send notification to a notifiable entity
     *
     * @param mixed $notifiable
     * @param BaseNotification $notification
     * @return ServiceResponse
     */
    public function send($notifiable, BaseNotification $notification): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($notifiable, $notification) {
            NotificationFacade::send($notifiable, $notification);

            return ServiceResponse::success([
                'notification_queued' => true,
            ], 'Notification queued successfully');
        }, 'send');
    }

    /**
     * Send notification via specific channels
     *
     * @param mixed $notifiable
     * @param BaseNotification $notification
     * @param array $channels
     * @return ServiceResponse
     */
    public function sendVia($notifiable, BaseNotification $notification, array $channels): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($notifiable, $notification, $channels) {
            // Override notification channels by reflection since channels is protected
            $reflection = new \ReflectionClass($notification);
            $property = $reflection->getProperty('channels');
            $property->setAccessible(true);
            $property->setValue($notification, $channels);

            NotificationFacade::send($notifiable, $notification);

            return ServiceResponse::success([
                'notification_queued' => true,
                'channels' => $channels,
            ], 'Notification queued via specified channels');
        }, 'sendVia');
    }

    /**
     * Send notification to an entity (polymorphic)
     *
     * @param string $entityType
     * @param int|string $entityId
     * @param BaseNotification $notification
     * @return ServiceResponse
     */
    public function sendToEntity(string $entityType, int|string $entityId, BaseNotification $notification): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($entityType, $entityId, $notification) {
            $entity = app($entityType)->find($entityId);

            if (!$entity) {
                return ServiceResponse::error('Entity not found', 404);
            }

            NotificationFacade::send($entity, $notification);

            return ServiceResponse::success([
                'notification_queued' => true,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
            ], 'Notification queued to entity');
        }, 'sendToEntity');
    }

    /**
     * Mark notification as read
     *
     * @param string $notificationId
     * @return ServiceResponse
     */
    public function markAsRead(string $notificationId): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($notificationId) {
            $success = $this->notificationRepository->markAsRead($notificationId);

            if (!$success) {
                return ServiceResponse::error('Notification not found', 404);
            }

            return ServiceResponse::success([], 'Notification marked as read');
        }, 'markAsRead');
    }

    /**
     * Get notifications for an entity
     *
     * @param string $entityType
     * @param int|string $entityId
     * @param int $perPage
     * @return ServiceResponse
     */
    public function getForEntity(string $entityType, int|string $entityId, int $perPage = 15): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($entityType, $entityId, $perPage) {
            $paginator = $this->notificationRepository->getForEntity($entityType, $entityId, $perPage);
            return $this->createPaginatedResponse($paginator, 'Notifications retrieved successfully');
        }, 'getForEntity');
    }

    /**
     * Get unread notifications for entity
     *
     * @param string $entityType
     * @param int|string $entityId
     * @param int $perPage
     * @return ServiceResponse
     */
    public function getUnreadForEntity(string $entityType, int|string $entityId, int $perPage = 15): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($entityType, $entityId, $perPage) {
            $paginator = $this->notificationRepository->getUnreadForEntity($entityType, $entityId, $perPage);

            return $this->createPaginatedResponse($paginator, 'Unread notifications retrieved successfully');
        }, 'getUnreadForEntity');
    }

    /**
     * Get unread count for entity
     *
     * @param string $entityType
     * @param int|string $entityId
     * @return ServiceResponse
     */
    public function getUnreadCount(string $entityType, int|string $entityId): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($entityType, $entityId) {
            $count = $this->notificationRepository->getUnreadCountForEntity($entityType, $entityId);

            return ServiceResponse::success(['count' => $count], 'Unread count retrieved');
        }, 'getUnreadCount');
    }

    /**
     * Mark all as read for entity
     *
     * @param string $entityType
     * @param int|string $entityId
     * @return ServiceResponse
     */
    public function markAllAsReadForEntity(string $entityType, int|string $entityId): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($entityType, $entityId) {
            $count = $this->notificationRepository->markAllAsReadForEntity($entityType, $entityId);

            return ServiceResponse::success(['count' => $count], 'All notifications marked as read');
        }, 'markAllAsReadForEntity');
    }

    /**
     * Retry failed notification
     *
     * @param string $notificationId
     * @return ServiceResponse
     */
    public function retry(string $notificationId): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($notificationId) {
            $notification = $this->notificationRepository->find($notificationId);

            if (!$notification) {
                return ServiceResponse::error('Notification not found', 404);
            }

            if (empty($notification->failed_channels)) {
                return ServiceResponse::error('No failed channels to retry', 400);
            }

            // Get the notifiable entity
            $notifiableType = $notification->notifiable_type;
            $notifiableId = $notification->notifiable_id;
            $notifiable = app($notifiableType)->find($notifiableId);

            if (!$notifiable) {
                return ServiceResponse::error('Notifiable entity not found', 404);
            }

            // Retry only failed channels
            $failedChannels = array_keys($notification->failed_channels);
            $results = [];

            foreach ($failedChannels as $channel) {
                $result = $this->providerManager->sendWithFailover(
                    $channel,
                    $notifiable,
                    $notification->data
                );
                $results[$channel] = $result;
            }

            // Update notification
            $successChannels = array_filter($results, fn($r) => $r['success']);
            $stillFailed = array_filter($results, fn($r) => !$r['success']);

            $notification->update([
                'channels_sent' => array_merge(
                    $notification->channels_sent ?? [],
                    array_keys($successChannels)
                ),
                'failed_channels' => !empty($stillFailed) ? $stillFailed : null,
                'sent_at' => !empty($successChannels) ? now() : $notification->sent_at,
            ]);

            return ServiceResponse::success([
                'successful_channels' => array_keys($successChannels),
                'failed_channels' => array_keys($stillFailed),
            ], 'Notification retry completed');
        }, 'retry');
    }

    /**
     * Resolve entity type from friendly name to full class name
     *
     * @param string $entityType
     * @return string
     */
    public function resolveEntityType(string $entityType): string
    {
        $allowedTypes = config('Notification.allowed_types');

        if (!isset($allowedTypes[$entityType])) {
            throw new \InvalidArgumentException("Invalid entity type: {$entityType}");
        }

        return $allowedTypes[$entityType];
    }
}
