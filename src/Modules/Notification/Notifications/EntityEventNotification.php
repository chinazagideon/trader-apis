<?php

namespace App\Modules\Notification\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class EntityEventNotification extends BaseNotification
{
    protected $entity;
    protected string $eventType;

    /**
     * Create a new notification instance.
     */
    public function __construct($entity, string $eventType, array $data = [], array $channels = [])
    {
        $this->entity = $entity;
        $this->eventType = $eventType;

        parent::__construct($data, $channels);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $template = $this->getTemplate($this->eventType);

        $subject = $template['subject'] ?? $this->data['subject'] ?? 'Notification';
        $body = $template['body'] ?? $this->data['message'] ?? '';

        // Replace placeholders
        $replacements = array_merge(
            ['id' => $this->entity->id ?? 'N/A'],
            $this->data
        );

        $subject = $this->replacePlaceholders($subject, $replacements);
        $body = $this->replacePlaceholders($body, $replacements);

        return (new MailMessage)
            ->subject($subject)
            ->line($body)
            ->when(isset($this->data['action_url']), function ($mail) {
                return $mail->action(
                    $this->data['action_text'] ?? 'View Details',
                    $this->data['action_url']
                );
            });
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        $template = $this->getTemplate($this->eventType);

        $title = $this->data['title'] ?? $template['subject'] ?? 'Notification';
        $message = $this->data['message'] ?? $template['body'] ?? '';

        $replacements = array_merge(
            ['id' => $this->entity->id ?? 'N/A'],
            $this->data
        );

        return [
            'title' => $this->replacePlaceholders($title, $replacements),
            'message' => $this->replacePlaceholders($message, $replacements),
            'event_type' => $this->eventType,
            'entity_type' => get_class($this->entity),
            'entity_id' => $this->entity->id ?? null,
            'data' => $this->data,
        ];
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms($notifiable): array
    {
        $template = $this->getTemplate($this->eventType);
        $message = $template['body'] ?? $this->data['message'] ?? '';

        $replacements = array_merge(
            ['id' => $this->entity->id ?? 'N/A'],
            $this->data
        );

        return [
            'message' => $this->replacePlaceholders($message, $replacements),
            'phone' => $notifiable->phone ?? $notifiable->phone_number ?? null,
        ];
    }

    /**
     * Get the push notification representation.
     */
    public function toPush($notifiable): array
    {
        $template = $this->getTemplate($this->eventType);

        $title = $this->data['title'] ?? $template['subject'] ?? 'Notification';
        $body = $template['body'] ?? $this->data['message'] ?? '';

        $replacements = array_merge(
            ['id' => $this->entity->id ?? 'N/A'],
            $this->data
        );

        return [
            'title' => $this->replacePlaceholders($title, $replacements),
            'body' => $this->replacePlaceholders($body, $replacements),
            'data' => $this->data,
        ];
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack($notifiable): array
    {
        $template = $this->getTemplate($this->eventType);
        $message = $template['body'] ?? $this->data['message'] ?? '';

        $replacements = array_merge(
            ['id' => $this->entity->id ?? 'N/A'],
            $this->data
        );

        return [
            'message' => $this->replacePlaceholders($message, $replacements),
            'blocks' => $this->data['blocks'] ?? null,
        ];
    }
}

