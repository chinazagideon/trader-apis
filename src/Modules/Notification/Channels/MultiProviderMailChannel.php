<?php

namespace App\Modules\Notification\Channels;

use App\Modules\Notification\Services\ProviderManager;
use App\Modules\Notification\Database\Models\Notification as NotificationModel;
use Illuminate\Notifications\Notification;

class MultiProviderMailChannel
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
        // Get email data from notification
        $data = method_exists($notification, 'toMail')
            ? $notification->toMail($notifiable)
            : $notification->toArray($notifiable);

        // Prepare email data
        $emailData = $this->prepareEmailData($notifiable, $data);

        // Send with failover
        $result = $this->providerManager->sendWithFailover('mail', $notifiable, $emailData);

        // Store notification in database
        $this->storeNotification($notifiable, $notification, $result);
    }

    /**
     * Prepare email data
     */
    protected function prepareEmailData($notifiable, $data): array
    {
        if (is_array($data)) {
            return array_merge([
                'email' => $notifiable->email ?? null,
                'subject' => $data['subject'] ?? 'Notification',
                'body' => $data['message'] ?? $data['body'] ?? '',
            ], $data);
        }

        // Handle MailMessage object
        return [
            'email' => $notifiable->email ?? null,
            'subject' => $data->subject ?? 'Notification',
            'body' => $data->introLines[0] ?? '',
            'view' => $data->view ?? null,
            'viewData' => $data->viewData ?? [],
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
            'channels_sent' => $result['success'] ? ['mail'] : [],
            'failed_channels' => $result['success'] ? null : [
                'mail' => $result['failed_providers'] ?? ['error' => $result['message'] ?? 'Unknown error']
            ],
            'sent_at' => $result['success'] ? now() : null,
            'metadata' => [
                'provider' => $result['provider'] ?? null,
            ],
        ]);
    }
}

