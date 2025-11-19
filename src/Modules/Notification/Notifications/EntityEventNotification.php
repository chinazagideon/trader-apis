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
        $template = $this->getTemplate($this->eventType, 'mail');

        // Prepare template data (objects for view, strings for placeholders)
        $templateData = $this->getTemplateData([
            'entity' => $this->entity,
            'entityId' => $this->entity->uuid ?? $this->entity->id ?? 'N/A',
            'entityType' => class_basename(get_class($this->entity)),
            'notifiable' => $notifiable,
            'eventType' => $this->eventType,
            'subject' => $template['subject'] ?? $this->data['subject'] ?? $this->getDefaultSubject(),
            'title' => $template['title'] ?? $this->data['title'] ?? $this->getDefaultTitle(),
            'action_url' => $this->data['action_url'] ?? null,
            'action_text' => $this->data['action_text'] ?? 'View Details',
            'name' => $this->data['name'] ?? ($notifiable->name ?? ($notifiable->email ?? 'User')),
            'email' => $this->data['email'] ?? ($notifiable->email ?? ''),
        ]);

        // Replace placeholders in subject (only string values)
        $subject = $this->replacePlaceholders($templateData['subject'], $this->getStringReplacements($templateData));

        // Get HTML view path
        $view = $this->getEmailView($this->eventType);

        // Build MailMessage with HTML view
        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->view($view, $templateData);

        // Add action button if provided
        if (isset($this->data['action_url'])) {
            $mailMessage->action(
                $this->data['action_text'] ?? 'View Details',
                $this->data['action_url']
            );
        }

        // Add greeting if in template
        if (isset($template['greeting'])) {
            $greeting = $this->replacePlaceholders($template['greeting'], $this->getStringReplacements($templateData));
            $mailMessage->greeting($greeting);
        }

        return $mailMessage;
    }

    /**
     * Get default subject based on event type
     */
    protected function getDefaultSubject(): string
    {
        return ucwords(str_replace('_', ' ', $this->eventType)) . ' Notification';
    }

    /**
     * Get default title based on event type
     */
    protected function getDefaultTitle(): string
    {
        return ucwords(str_replace('_', ' ', $this->eventType));
    }

    /**
     * Extract only string values from template data for placeholder replacement
     * Filters out objects, arrays, and null values
     */
    protected function getStringReplacements(array $templateData): array
    {
        $replacements = [];

        foreach ($templateData as $key => $value) {
            // Only include string, numeric, or boolean values
            if (is_string($value) || is_numeric($value) || is_bool($value)) {
                $replacements[$key] = (string) $value;
            }
            // Handle special cases
            elseif (is_null($value)) {
                $replacements[$key] = '';
            }
            // Skip objects and arrays - they're for view context only
        }

        return $replacements;
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
            ['id' => $this->entity->uuid ?? $this->entity->id ?? 'N/A'],
            $this->data
        );

        return [
            'title' => $this->replacePlaceholders($title, $replacements),
            'message' => $this->replacePlaceholders($message, $replacements),
            'event_type' => $this->eventType,
            'entity_type' => get_class($this->entity),
            'entity_id' => $this->entity->uuid ?? $this->entity->id ?? null,
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
            ['id' => $this->entity->uuid ?? $this->entity->id ?? 'N/A'],
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
            ['id' => $this->entity->uuid ?? $this->entity->id ?? 'N/A'],
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
            ['id' => $this->entity->uuid ?? $this->entity->id ?? 'N/A'],
            $this->data
        );

        return [
            'message' => $this->replacePlaceholders($message, $replacements),
            'blocks' => $this->data['blocks'] ?? null,
        ];
    }
}

