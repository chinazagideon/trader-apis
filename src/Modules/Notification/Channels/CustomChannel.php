<?php

namespace App\Modules\Notification\Channels;

use App\Modules\Notification\Services\ProviderManager;
use App\Modules\Notification\Database\Models\Notification as NotificationModel;
use Illuminate\Notifications\Notification;

/**
 * Generic custom channel for Push and Slack notifications
 */
class CustomChannel
{
    protected ProviderManager $providerManager;
    protected string $channelType;

    public function __construct(ProviderManager $providerManager, string $channelType = 'push')
    {
        $this->providerManager = $providerManager;
        $this->channelType = $channelType;
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification): void
    {
        $methodName = 'to' . ucfirst($this->channelType);

        // Get notification data
        $data = method_exists($notification, $methodName)
            ? $notification->$methodName($notifiable)
            : $notification->toArray($notifiable);

        // Prepare notification data
        $preparedData = $this->prepareData($notifiable, $data);

        // Send with failover
        $result = $this->providerManager->sendWithFailover($this->channelType, $notifiable, $preparedData);

        // Store notification in database
        $this->storeNotification($notifiable, $notification, $result);
    }

    /**
     * Prepare notification data
     */
    protected function prepareData($notifiable, $data): array
    {
        if (is_array($data)) {
            return array_merge($this->getNotifiableData($notifiable), $data);
        }

        return array_merge(
            $this->getNotifiableData($notifiable),
            ['message' => (string) $data]
        );
    }

    /**
     * Get notifiable-specific data
     */
    protected function getNotifiableData($notifiable): array
    {
        return match($this->channelType) {
            'push' => [
                'device_token' => $notifiable->device_token ?? $notifiable->fcm_token ?? null,
                'platform' => $notifiable->platform ?? 'android',
            ],
            'slack' => [
                'channel' => $notifiable->slack_channel ?? null,
                'webhook' => $notifiable->slack_webhook ?? null,
            ],
            default => [],
        };
    }

    /**
     * Store notification in database
     */
    protected function storeNotification($notifiable, Notification $notification, array $result): void
    {
        NotificationModel::create([
            'id' => $notification->id,
            'type' => get_class($notification),
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->getKey(),
            'data' => method_exists($notification, 'toArray')
                ? $notification->toArray($notifiable)
                : [],
            'channels_sent' => $result['success'] ? [$this->channelType] : [],
            'failed_channels' => $result['success'] ? null : [
                $this->channelType => $result['failed_providers'] ?? ['error' => $result['message'] ?? 'Unknown error']
            ],
            'sent_at' => $result['success'] ? now() : null,
            'metadata' => [
                'provider' => $result['provider'] ?? null,
            ],
        ]);
    }

    /**
     * Create a Push channel instance
     */
    public static function push(ProviderManager $providerManager): self
    {
        return new self($providerManager, 'push');
    }

    /**
     * Create a Slack channel instance
     */
    public static function slack(ProviderManager $providerManager): self
    {
        return new self($providerManager, 'slack');
    }
}

