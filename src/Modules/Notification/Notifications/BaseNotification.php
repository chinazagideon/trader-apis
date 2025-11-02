<?php

namespace App\Modules\Notification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Modules\Notification\Database\Models\NotificationConfig;

abstract class BaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $data = [];
    protected array $channels = [];
    protected ?string $template = null;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $data = [], array $channels = [])
    {
        $this->data = $data;
        $this->channels = $channels;

        // Set queue configuration
        $this->onConnection(config('notification.queue.connection', 'redis'));
        $this->onQueue(config('notification.queue.name', 'notifications'));
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        if (!empty($this->channels)) {
            return $this->channels;
        }

        return config('notification.channels', ['database']);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return $this->data;
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'title' => $this->data['title'] ?? 'Notification',
            'message' => $this->data['message'] ?? '',
            'data' => $this->data,
            'type' => get_class($this),
        ];
    }

    /**
     * Get template from database or config
     */
    protected function getTemplate(string $templateKey): ?array
    {
        // Try database first
        $dbTemplate = NotificationConfig::active()
            ->where('type', 'template')
            ->where('name', $templateKey)
            ->first();

        if ($dbTemplate) {
            return $dbTemplate->config;
        }

        // Fallback to config
        return config("notification.templates.{$templateKey}");
    }

    /**
     * Replace placeholders in template
     */
    protected function replacePlaceholders(string $text, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $text = str_replace(':' . $key, $value, $text);
        }

        return $text;
    }

    /**
     * Get notification data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get notification channels
     */
    public function getChannels(): array
    {
        return $this->channels;
    }
}

