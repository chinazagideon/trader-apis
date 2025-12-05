<?php

namespace App\Modules\Funding\Events;


use App\Modules\Funding\Database\Models\Funding;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Core\Events\BaseNotificationEvent;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use App\Modules\User\Database\Models\User;
class FundingWasCompleted extends BaseNotificationEvent implements ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Constructor
     * @param Funding $funding
     * @param string $moduleName
     */
    public function __construct(
        public Funding $funding,
        public string $moduleName,
        public string $operation = 'create'
    ) {}

    /**
     * Get the entity
     *
     * @return Funding
     */
    public function getEntity()
    {
        return $this->funding;
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
     * Prepare notification data
     *
     * @return array
     */
    public function getPreparedNotificationData(): array
    {
        return [
            'type' => $this->funding?->type ?? 'unknown',
            'notification_channels' => $this->getChannels(),
            'notification_title' => $this->getTitle(),
            'notification_message' => $this->getMessage(),
            'action_url' => $this->getActionUrl(),
            'action_text' => $this->getActionText(),
            'source' => 'funding_completed',
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
        return 'funding_completed';
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
     * Get the action url
     *
     * @return string
     */
    public function getActionUrl(): string
    {
        return "n/a";
    }

    /**
     * Get the title
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'Funding Completed Request Received';
    }

    /**
     * Get the message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return 'Your funding request has been received and is being processed.
        You will be notified once the funding is completed.';
    }

    /**
     * Get the notifiable
     *
     * @return User
     */
    public function getNotifiable(): User
    {
        return $this->funding->fundable;
    }

    /**
     * Get the serialized data
     *
     * @return array
     */
    public function getSerializedData(): array
    {
        return [
            'funding_id' => $this->funding->id,
            'uuid' => $this->funding->uuid,
            'fundable_id' => $this->funding->fundable_id,
            'fundable_type' => $this->funding->fundable_type,
            'amount' => $this->funding->amount,
            'fiat_amount' => $this->funding->fiat_amount,
            'status' => $this->funding->status,
            'currency' => $this->funding->currency ?? null,
            'fiat_currency' => $this->funding->fiatCurrency ?? null,
            'client_id' => $this->funding->client_id ?? null,
        ];
    }
}
