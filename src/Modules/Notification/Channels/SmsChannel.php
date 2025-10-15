<?php

namespace App\Modules\Notification\Channels;

use App\Modules\Notification\Services\ProviderManager;
use App\Modules\Notification\Database\Models\Notification as NotificationModel;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    protected ProviderManager $providerManager;

    public function __construct(ProviderManager $providerManager)
    {
        $this->providerManager = $providerManager;
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification): void
    {
        // Get SMS data from notification
        $data = method_exists($notification, 'toSms')
            ? $notification->toSms($notifiable)
            : $notification->toArray($notifiable);

        // Prepare SMS data
        $smsData = $this->prepareSmsData($notifiable, $data);

        // Send with failover
        $result = $this->providerManager->sendWithFailover('sms', $notifiable, $smsData);

        // Store notification in database
        $this->storeNotification($notifiable, $notification, $result);
    }

    /**
     * Prepare SMS data
     */
    protected function prepareSmsData($notifiable, $data): array
    {
        if (is_array($data)) {
            return array_merge([
                'phone' => $notifiable->phone ?? $notifiable->phone_number ?? null,
                'message' => $data['message'] ?? $data['body'] ?? '',
            ], $data);
        }

        return [
            'phone' => $notifiable->phone ?? $notifiable->phone_number ?? null,
            'message' => (string) $data,
        ];
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
            'channels_sent' => $result['success'] ? ['sms'] : [],
            'failed_channels' => $result['success'] ? null : [
                'sms' => $result['failed_providers'] ?? ['error' => $result['message'] ?? 'Unknown error']
            ],
            'sent_at' => $result['success'] ? now() : null,
            'metadata' => [
                'provider' => $result['provider'] ?? null,
            ],
        ]);
    }
}

