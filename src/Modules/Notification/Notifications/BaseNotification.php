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
        // Ensure job is dispatched only after DB commit to avoid race conditions
        $this->afterCommit();
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
    protected function getTemplate(string $templateKey, string $channel = 'mail'): ?array
    {
        // Try database first
        $dbTemplate = NotificationConfig::active()
            ->where('type', 'template')
            ->where('name', $templateKey)
            ->where('channel', $channel)
            ->first();

        if ($dbTemplate) {
            return $dbTemplate->config;
        }

        // Fallback to config
        return config("notification.templates.{$templateKey}");
    }

    /**
     * Get HTML view path for email template
     * Supports: view path, database config, or default
     */
    protected function getEmailView(string $templateKey): string
    {
        $template = $this->getTemplate($templateKey, 'mail');

        // If template has a view path, use it
        if (isset($template['view'])) {
            return $template['view'];
        }

        // Default view path: notification::emails.{templateKey}
        return "notification::emails.{$templateKey}";
    }

    /**
     * Get template data for rendering
     */
    protected function getTemplateData(array $additionalData = []): array
    {
        return array_merge($this->data, $additionalData);
    }

    /**
     * Replace placeholders in template
     * Only replaces string values - filters out objects, arrays, null, etc.
     */
    protected function replacePlaceholders(string $text, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            // Only replace if value is a string or can be converted to string
            if (is_string($value) || is_numeric($value) || is_bool($value)) {
                $text = str_replace(':' . $key, (string) $value, $text);
            }
            // Skip objects, arrays, null, etc. - they're passed to views directly
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

