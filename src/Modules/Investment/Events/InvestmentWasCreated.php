<?php

namespace App\Modules\Investment\Events;

use App\Core\Events\BaseNotificationEvent;
use App\Modules\Investment\Database\Models\Investment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Support\Facades\Log;
use App\Modules\User\Database\Models\User;
class InvestmentWasCreated extends BaseNotificationEvent implements ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Investment $investment,
        public array $metadata = [],
        public string $operation = 'create'
    ) {}

    /**
     * Get the entity
     *
     * @return Investment
     */
    public function getEntity()
    {
        return $this->investment;
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
            'source' => 'investment_created',
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
        return 'investment_was_created';
    }

    /**
     * Get the action text
     *
     * @return string
     */
    public function getActionText(): string
    {
        return 'View Investment';
    }

    /**
     * Get the action url
     *
     * @return string
     */
    public function getActionUrl(): string
    {
        return "not available";
    }

    /**
     * Get the title
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'New Investment Created';
    }

    /**
     * Get the message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return 'A new investment has been created successfully.';
    }

    /**
     * Get the notifiable (the User who should receive the notification)
     * CRITICAL: This MUST return a User, not the Investment, because Users have email addresses
     *
     * IMPORTANT: This method is called AFTER the event is unserialized in the queue worker,
     * so we must reload the user relationship from the database.
     *
     * @return ?User
     */
    public function getNotifiable(): ?User
    {
        $user = $this->metadata['user'] ?? null;
        if ($user) {
            return $user;
        }
        return null;
    }
}
