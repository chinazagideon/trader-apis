<?php

namespace App\Modules\Notification\Channels;

use App\Modules\Notification\Services\ProviderManager;
use App\Modules\Notification\Database\Models\Notification as NotificationModel;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MultiProviderMailChannel
{
    protected ProviderManager $providerManager;

    public function __construct(ProviderManager $providerManager)
    {
        $this->providerManager = $providerManager;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param Notification|EntityEventNotification $notification
     */
    public function send($notifiable, Notification $notification): void
    {
        // Get email data from notification
        $mailMessage = null;

        if (method_exists($notification, 'toMail')) {
            /** @var MailMessage|null $mailMessage */
            $mailMessage = $notification->toMail($notifiable);
        }

        if (!$mailMessage instanceof MailMessage) {
            // Fallback for array data
            $emailData = $this->prepareEmailData($notifiable, $notification->toArray($notifiable));
            $result = $this->providerManager->sendWithFailover('mail', $notifiable, $emailData);
        } else {
            // Extract view and data from MailMessage and use ProviderManager
            $emailData = $this->prepareEmailDataFromMailMessage($notifiable, $mailMessage);
            $result = $this->providerManager->sendWithFailover('mail', $notifiable, $emailData);
        }

        // Store notification in database
        $this->storeNotification($notifiable, $notification, $result);
    }

    /**
     * Prepare email data from MailMessage object
     * Uses reflection to access protected properties
     */
    protected function prepareEmailDataFromMailMessage($notifiable, MailMessage $mailMessage): array
    {
        // Use reflection to access protected properties
        $reflection = new \ReflectionClass($mailMessage);

        // Get view property
        $viewProperty = $reflection->getProperty('view');
        $viewProperty->setAccessible(true);
        $view = $viewProperty->getValue($mailMessage);

        // Get viewData property
        $viewDataProperty = $reflection->getProperty('viewData');
        $viewDataProperty->setAccessible(true);
        $viewData = $viewDataProperty->getValue($mailMessage) ?? [];

        // Get subject (public property)
        $subject = $mailMessage->subject ?? 'Notification';

        return [
            'email' => $notifiable->email ?? null,
            'subject' => $subject,
            'view' => $view,
            'viewData' => $viewData,
        ];
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

        // Handle MailMessage object (fallback - should not be reached)
        return [
            'email' => $notifiable->email ?? null,
            'subject' => $data->subject ?? 'Notification',
            'body' => $data->introLines[0] ?? '',
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
