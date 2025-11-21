<?php

namespace App\Modules\Notification\Listeners;

use App\Core\Traits\ConfigurableListener;
use App\Core\Contracts\ConfigurableListenerInterface;
use App\Core\Contracts\NotificationEventsContract;
use App\Core\Exceptions\AppException;
use App\Modules\Notification\Services\NotificationService;
use App\Modules\Notification\Notifications\EntityEventNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Core\Services\LoggingService;
use App\Modules\Notification\Contracts\NotificationOutboxPublisherInterface;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

class SendEntityNotification implements ConfigurableListenerInterface, ShouldDispatchAfterCommit
{
    use ConfigurableListener, Queueable, InteractsWithQueue, SerializesModels;

    /**
     * Configuration keys for this listener
     */
    protected ?string $eventConfigKey = null;
    protected string $listenerConfigKey = 'send_notification';

    /**
     * The number of times the job may be attempted.
     */
    public int $tries;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public array|int $backoff;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout;

    public function __construct(
        private NotificationService $notificationService,
        private LoggingService $logger,
        private NotificationOutboxPublisherInterface $outboxPublisher
    ) {
        // Initialize queue properties from configuration
        $this->tries = $this->getTries();
        $this->backoff = $this->getBackoff();
        $this->timeout = $this->getTimeout();

        // Set queue connection and queue name using Queueable trait methods
        $connection = $this->getQueueConnection() ?? 'redis';

        // For notification listeners, always use 'notifications' queue if config can't be read
        $queue = $this->getQueue();
        if (!$queue || $queue === 'default') {
            $queue = 'notifications';
        }

        $this->onConnection($connection);
        $this->onQueue($queue);

        Log::info('[SendEntityNotification] Listener initialized', [
            'connection' => $connection,
            'queue' => $queue,
            'tries' => $this->tries,
        ]);
    }

    /**
     * Handle the event.
     * @param NotificationEventsContract $event
     * @return void
     */
    public function handle(NotificationEventsContract $event): void
    {
        $this->logger->startPerformanceTracking();

        Log::info('SendEntityNotification listener started', [
            'event_class' => get_class($event),
            'event_type' => method_exists($event, 'getEventType') ? $event->getEventType() : 'unknown',
            'listener_class' => static::class,
            'queue_connection' => $this->connection ?? 'unknown',
            'queue_name' => $this->queue ?? 'unknown',
        ]);

        // Get entity from event
        $entity = $event->getEntity();

        if (!$entity) {
            throw new AppException("No entity found in event");
        }

        // Get notifiable from contract
        $notifiable = $event->getNotifiable();

        Log::info('Notifiable', [
            'notifiable' => $notifiable,
            'entity' => $entity,
            'event' => $event,
            'event_type' => $event->getEventType(),
            'entity_type' => get_class($entity),
            'entity_id' => $entity->id ?? null,
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->id ?? null,
            'notifiable_client_name' => $event->getNotifiableClientName(),
            'metadata' => $event->getMetadata(),
        ]);


        if (!$notifiable) {
            Log::warning("No notifiable found for notification", [
                'event_type' => $event->getEventType(),
                'entity_type' => get_class($entity),
            ]);
            return;
        }

        // Get event type from contract (normalized)
        $eventType = $event->getEventType();

        // Get channels from contract
        $channels = $event->getChannels();

        // Prepare notification data using contract methods
        $notificationData = $this->prepareNotificationDataFromContract($event, $entity);

        // Build notification for payload preparation only
        $notification = new EntityEventNotification($entity, $eventType, $notificationData, $channels);

        Log::info('Notification Trace', [
            'stage' => 'listener_publishing_outbox',
            'event_type' => $eventType,
            'notifiable_type' => method_exists($notifiable, 'getMorphClass') ? $notifiable->getMorphClass() : get_class($notifiable),
            'notifiable_id' => $notifiable->id ?? null,
            'channels' => $channels,
        ]);

        // Publish to outbox; consumer will persist DB notification and send emails
        try {
            $this->outboxPublisher->publish(
                eventType: $eventType,
                notifiable: $notifiable,
                channels: $channels,
                payload: [
                    'to_database' => $notification->toDatabase($notifiable),
                    'mail_data' => $notificationData,
                ],
                entityType: get_class($entity),
                entityId: $entity->id ?? null
            );
        } catch (\Exception $e) {
            Log::error('Failed to publish notification to outbox', [
                'event_type' => $eventType,
                'notifiable_type' => get_class($notifiable),
                'notifiable_id' => $notifiable->id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        Log::info("Entity notification sent", [
            'event_type' => $eventType,
            'entity_type' => get_class($entity),
            'entity_id' => $entity->id ?? null,
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->id ?? null,
            'channels' => $channels,
        ]);
    }

    /**
     * Prepare notification data from contract methods
     * No more method_exists checks - contract guarantees these methods exist
     */
    protected function prepareNotificationDataFromContract(
        NotificationEventsContract $event,
        $entity
    ): array {
        return [
            'title' => $event->getTitle(),
            'message' => $event->getMessage(),
            'action_url' => $event->getActionUrl(),
            'action_text' => $event->getActionText(),
            'entity_id' => $entity->id ?? null,
            'entity_type' => get_class($entity),
            'notifiable_client_name' => $event->getNotifiableClientName(),
            ...$event->getMetadata(),
        ];
    }

    /**
     * Handle a job failure.
     */
    public function failed(NotificationEventsContract $event, $exception): void
    {
        Log::error("Entity notification failed", [
            'event' => get_class($event),
            'event_type' => $event->getEventType(),
            'exception' => $exception->getMessage(),
            'exception_class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    /**
     * Prepare notification data
     */
    protected function prepareNotificationData($entity, string $eventType, array $metadata): array
    {
        $baseData = [
            'title' => $metadata['notification_title'] ?? $this->getDefaultTitle($eventType),
            'message' => $metadata['notification_message'] ?? $this->getDefaultMessage($eventType, $entity),
            'action_url' => $metadata['action_url'] ?? null,
            'action_text' => $metadata['action_text'] ?? 'View Details',
            'entity_id' => $entity->id ?? null,
            'entity_type' => get_class($entity),
        ];

        // Add user-specific data for user_was_created event
        if ($eventType === 'user_was_created' && method_exists($entity, 'name')) {
            $baseData['name'] = $entity->name ?? $entity->email ?? 'User';
            $baseData['email'] = $entity->email ?? null;
            $baseData['action_url'] = $metadata['action_url'] ?? null;
            $baseData['action_text'] = $metadata['action_text'] ?? 'Go to Dashboard';
        }

        return $baseData;
    }

    /**
     * Get default title for event type
     */
    protected function getDefaultTitle(string $eventType): string
    {
        return ucwords(str_replace('_', ' ', $eventType));
    }

    /**
     * Get default message for event type
     */
    protected function getDefaultMessage(string $eventType, $entity): string
    {
        $id = $entity->id ?? 'N/A';
        $type = class_basename(get_class($entity));

        return "{$type} #{$id} - " . ucwords(str_replace('_', ' ', $eventType));
    }
}
