<?php

namespace App\Modules\User\Events;

use App\Core\Events\BaseNotificationEvent;
use App\Core\Contracts\NotificationEventsContract;
use App\Modules\User\Database\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

/**
 * Event for user creation
 */
class UserWasCreatedEvent extends BaseNotificationEvent implements ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    /**
     * Constructor
     * @param User $user
     * @param string $operation
     */
    public function __construct(
        public User $user,
        public string $operation = 'create'
    ) {}

    /**
     * Get the entity
     *
     * @return User
     */
    public function getEntity()
    {
        return $this->user;
    }


    /**
     * Get the metadata
     *
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->getPreparedNotificationData();
    }

    /**
     * Prepare notification data
     *
     * @return array
     */
    public function getPreparedNotificationData(): array
    {
        return [
            'notification_channels' => $this->getChannels(), // Send via email and database
            'notification_title' => $this->getTitle(),
            'notification_message' => $this->getMessage(),
            'action_url' => $this->getActionUrl(),
            'action_text' => $this->getActionText(),
            'source' => 'registration',
        ];
    }

    /**
     * Get the channels
     *
     * @return array
     */
    public function getChannels(): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the title
     *
     * @return string
     */
    public function getEventType(): string
    {
        return 'user_was_created';
    }

    /**
     * Get the action text
     *
     * @return string
     */
    public function getActionText(): string
    {
        return 'Login';
    }

    /**
     * Get the action url
     *
     * @return string
     */
    public function getActionUrl(): string
    {
        return route('login');
    }

    /**
     * Get the title
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'Welcome,' . ($this->getEntity()->name ?? 'User') . '!';
    }

    /**
     * Get the message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return 'Your account has been created successfully.
        Welcome to our platform! You can now login to your account to continue.';
    }

    /**
     * Get the notifiable client
     *
     * @return string|null
     */
    public function getNotifiableClientName(): ?string
    {
        return $this->getNotifiable()?->client?->name ?? null;
    }
}
