<?php

namespace App\Modules\Withdrawal\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use App\Modules\Withdrawal\Database\Models\Withdrawal;
use App\Core\Events\BaseNotificationEvent;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use App\Modules\User\Database\Models\User;

class WithdrawalWasCompleted extends BaseNotificationEvent implements ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Constructor
     *
     * @param Withdrawal $withdrawal
     */
    public function __construct(public Withdrawal $withdrawal) {}

    /**
     * Get the entity
     *
     * @return Withdrawal
     */
    public function getEntity()
    {
        return $this->withdrawal;
    }

    /**
     * Get the metadata
     *
     * @return array
     */
    public function getMetadata(): array
    {
        return array_merge($this->getPreparedNotificationData(), $this->getSerializedData());
    }

    /**
     * Get the prepared notification data
     *
     * @return array
     */
    public function getPreparedNotificationData(): array
    {
        return [
            'notification_channels' => $this->getChannels(),
            'notification_title' => $this->getTitle(),
            'notification_message' => $this->getMessage(),
            'action_url' => $this->getActionUrl(),
            'action_text' => $this->getActionText(),
            'source' => 'withdrawal_completed',
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
    public function getTitle(): string
    {
        return 'Withdrawal Request Received';
    }

    /**
     * Get the message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return 'Your withdrawal request has been received and is being processed.
        You will be notified once the withdrawal is completed.';
    }

    /**
     * Get the action url
     *
     * @return string
     */
    public function getActionUrl(): string
    {
        return "n/a";
    }

    /**
     * Get the action text
     *
     * @return string
     */
    public function getActionText(): string
    {
        return 'View Details';
    }

    /**
     * Get the event type
     *
     * @return string
     */
    public function getEventType(): string
    {
        return 'withdrawal_completed';
    }

    /**
     * Get the serialized data
     *
     * @return array
     */
    public function getSerializedData(): array
    {
        return [
            'withdrawal_id' => $this->withdrawal->id,
            'uuid' => $this->withdrawal->uuid,
            'currency' => $this->withdrawal->currency ?? null,
            'fiat_currency' => $this->withdrawal->fiatCurrency ?? null,
            'amount' => $this->withdrawal->amount,
            'fiat_amount' => $this->withdrawal->fiat_amount,
            'status' => $this->withdrawal->status,
            'notes' => $this->withdrawal->notes,
        ];
    }

    /**
     * Get the notifiable
     *
     * @return User
     */
    public function getNotifiable(): User
    {
        return $this->withdrawal->withdrawable;
    }

    /**
     * Get the notifiable client name
     *
     * @return string|null
     */
    public function getNotifiableClientName(): ?string
    {
        // return $this->withdrawal->withdrawable->name;
        return null;
    }
}
