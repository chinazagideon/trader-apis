<?php

namespace App\Modules\Notification\Listeners;

use App\Core\Traits\ConfigurableListener;
use App\Core\Contracts\ConfigurableListenerInterface;
use App\Modules\Notification\Services\NotificationService;
use App\Modules\Notification\Notifications\EntityEventNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use App\Core\Services\LoggingService;

class SendEntityNotification implements ConfigurableListenerInterface, ShouldQueue
{
    use ConfigurableListener;

    /**
     * Configuration keys for this listener
     */
    protected string $eventConfigKey = 'investment_created'; // Default, can be overridden
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

    /**
     * The name of the queue connection to use.
     */
    public ?string $connection;

    /**
     * The name of the queue to push the job to.
     */
    public ?string $queue;

    public function __construct(
        private NotificationService $notificationService,
        private LoggingService $logger,

    ) {
        // Initialize queue properties from configuration
        $this->tries = $this->getTries();
        $this->backoff = $this->getBackoff();
        $this->timeout = $this->getTimeout();
        $this->connection = $this->getQueueConnection();
        $this->queue = $this->getQueue();
    }

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        $this->logger->startPerformanceTracking();

        $this->logger->logOperation(
            'SendEntityNotification',
            'handle',
            'start',
            "Starting entity notification",
            [
                'event' =>  json_encode($event),
                'operation' => 'handle',
            ]
        );

        // Determine event type from event class
        $eventClass = get_class($event);
        $eventType = $this->getEventTypeFromClass($eventClass);

        // Get entity from event
        $entity = $this->getEntityFromEvent($event);

        if (!$entity) {
            Log::warning("No entity found in event", ['event' => $eventClass]);
            return;
        }

        // Get metadata
        $metadata = method_exists($event, 'getMetadata') ? $event->getMetadata() : [];

        // Determine channels from config or metadata
        $channels = $metadata['notification_channels'] ?? config("notification.channels", ['database']);

        // Create notification data
        $notificationData = $this->prepareNotificationData($entity, $eventType, $metadata);

        // Create and send notification
        $notification = new EntityEventNotification(
            $entity,
            $eventType,
            $notificationData,
            $channels
        );

        // Send to the entity itself (polymorphic)
        $this->notificationService->send($entity, $notification);

        Log::info("Entity notification sent", [
            'event_type' => $eventType,
            'entity_type' => get_class($entity),
            'entity_id' => $entity->id ?? null,
            'channels' => $channels,
        ]);
    }

    /**
     * Get event type from event class name
     */
    protected function getEventTypeFromClass(string $eventClass): string
    {
        // Extract event name from class (e.g., InvestmentCreated -> investment_created)
        $parts = explode('\\', $eventClass);
        $className = end($parts);

        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $className));
    }

    /**
     * Get entity from event
     */
    protected function getEntityFromEvent($event)
    {
        // Try common entity properties
        if (isset($event->entity)) {
            return $event->entity;
        }

        if (isset($event->investment)) {
            return $event->investment;
        }

        if (isset($event->transaction)) {
            return $event->transaction;
        }

        if (isset($event->payment)) {
            return $event->payment;
        }

        // Try to get from first public property
        $reflection = new \ReflectionClass($event);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        if (!empty($properties)) {
            $firstProperty = $properties[0];
            return $firstProperty->getValue($event);
        }

        return null;
    }

    /**
     * Prepare notification data
     */
    protected function prepareNotificationData($entity, string $eventType, array $metadata): array
    {
        return [
            'title' => $metadata['notification_title'] ?? $this->getDefaultTitle($eventType),
            'message' => $metadata['notification_message'] ?? $this->getDefaultMessage($eventType, $entity),
            'action_url' => $metadata['action_url'] ?? null,
            'action_text' => $metadata['action_text'] ?? 'View Details',
            'entity_id' => $entity->id ?? null,
            'entity_type' => get_class($entity),
        ];
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

    /**
     * Handle a job failure.
     */
    public function failed($event, $exception): void
    {
        Log::error("Entity notification failed", [
            'event' => get_class($event),
            'exception' => $exception->getMessage(),
        ]);
    }
}
