<?php

namespace App\Modules\Payment\Events;

use App\Modules\Payment\Database\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use App\Core\Events\BaseNotificationEvent;
use App\Modules\User\Database\Models\User;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Database\Eloquent\Model;
use App\Modules\User\Enums\UserPaymentTypes;
use App\Modules\User\Facade\UserRepositoryFacade;
use App\Modules\Currency\Facades\CurrencyRepositoryFacade;

class PaymentWasCompleted extends BaseNotificationEvent implements ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Payment $payment
    ) {

        $this->payment->loadMissing('currency');
    }


    /**
     * Get the entity
     *
     * @return Payment
     */
    public function getEntity()
    {
        return $this->payment;
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
            'notification_channels' => $this->getChannels(),
            'notification_title' => $this->getTitle(),
            'notification_message' => $this->getMessage(),
            'action_url' => $this->getActionUrl(),
            'action_text' => $this->getActionText(),
            'source' => 'payment_was_completed',
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
        return 'payment_was_completed';
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
        return 'Payment Completed Successfully';
    }

    /**
     * Get the message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return 'Your payment has been completed successfully.';
    }


    /**
     * Get the serialized data
     *
     * @return array
     */
    public function getSerializedData(): array
    {
        return [
            'user_id' => $this->getUserIdByPayableType(),
            'payment_id' => $this->payment->id,
            'payable_id' => $this->payment->payable_id,
            'payable_type' => $this->payment->payable_type,
            'status' => $this->payment->status,
            // 'type' => $this->payment->payable_type, // Keep for backward compatibility
            'amount' => $this->payment->amount,
            'hash' => $this->payment->hash ?? null,
            'currency' => $this->payment->currency,
            'method' => $this->payment->method ?? null,
            'uuid' => $this->payment->uuid,
        ];
    }

    /**
     * Get the user id
     *
     * @return int
     */
    public function getUserIdByPayableType(): int
    {
        $payable = $this->payment->payable;

        if (!$payable) {
            throw new \Exception('Payable not found for payment');
        }

        if (!isset($payable->user_id)) {
            throw new \Exception('User ID not available on payable for payment');
        }

        return (int) $payable->user_id;
    }


    /**
     * Get the notifiable
     *
     * @return Model
     */
    public function getNotifiable(): Model
    {
        return UserRepositoryFacade::getModelById($this->getUserIdByPayableType());
    }

    /**
     * Get the notifiable client name
     *
     * @return string|null
     */
    public function getNotifiableClientName(): ?string
    {
        return null;
        // return ucwords(UserModelFacade::find($this->getUserIdByPayableType())?->client?->name ?? null);
    }
}
