<?php

namespace App\Modules\Notification\Contracts;

/**
 * Interface for publishing notifications to the outbox
 */
interface NotificationOutboxPublisherInterface
{
    /**
     * Publish a notification to the outbox
     * @param string $eventType
     * @param mixed $notifiable
     * @param array $channels
     * @param array $payload
     * @param string|null $entityType
     * @param mixed|null $entityId
     * @param string|null $dedupeKey
     * @return void
     */
    public function publish(
        string $eventType,
        $notifiable,
        array $channels,
        array $payload = [],
        ?string $entityType = null,
        $entityId = null,
        ?string $dedupeKey = null
    ): void;
}



