<?php

namespace App\Modules\Notification\Services;

use App\Modules\Notification\Contracts\NotificationOutboxPublisherInterface;
use App\Modules\Notification\Database\Models\NotificationOutbox;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class NotificationOutboxPublisher implements NotificationOutboxPublisherInterface
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
    ): void {

        $notifiableType = method_exists($notifiable, 'getMorphClass')
            ? $notifiable->getMorphClass()
            : get_class($notifiable);

        Log::info('NotifTrace', [
            'stage' => 'outbox_published',
            'event_type' => $eventType,
            'notifiable_type' => $notifiableType,
            'notifiable_id' => $notifiable->id,
            'notifiable_class' => get_class($notifiable),
            'notifiable_has_email' => method_exists($notifiable, 'getEmailForPasswordReset') ? 'yes' : 'no',
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'channels' => $channels,
        ]);

        NotificationOutbox::firstOrCreate(
            ['dedupe_key' => $dedupeKey ?? $this->makeDedupeKey($eventType, $notifiableType, $notifiable->id, $entityType, $entityId)],
            [
                'event_type' => $eventType,
                'notifiable_type' => $notifiableType,
                'notifiable_id' => $notifiable->id,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'channels' => array_values(array_unique($channels)),
                'payload' => $payload,
                'status' => 'pending',
                'available_at' => now(),
            ]
        );
    }

    /**
     * Make a dedupe key
     * @param string $eventType
     * @param string $notifiableType
     * @param mixed $notifiableId
     * @param string|null $entityType
     * @param mixed|null $entityId
     * @return string
     */
    protected function makeDedupeKey(string $eventType, string $notifiableType, $notifiableId, ?string $entityType, $entityId): string
    {
        return Str::of($eventType . '|' . $notifiableType . '|' . $notifiableId . '|' . $entityType . '|' . $entityId)->slug('|');
    }
}
