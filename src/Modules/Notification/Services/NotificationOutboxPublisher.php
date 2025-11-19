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


        try {
            $outbox = NotificationOutbox::firstOrCreate(
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

            Log::info('NotifTrace', [
                'stage' => 'outbox_record_created',
                'outbox_id' => $outbox->id,
                'was_newly_created' => $outbox->wasRecentlyCreated,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create notification outbox record', [
                'event_type' => $eventType,
                'notifiable_type' => $notifiableType,
                'notifiable_id' => $notifiable->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
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
