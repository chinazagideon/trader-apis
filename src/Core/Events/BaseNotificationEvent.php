<?php

namespace App\Core\Events;

use App\Core\Contracts\NotificationEventsContract;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class BaseNotificationEvent implements NotificationEventsContract
{
    use Dispatchable, SerializesModels;

    /**
     * Get the event type key for template lookup
     * Override in child classes
     */
    abstract public function getEventType(): string;

    /**
     * Get the entity associated with this event
     * Override in child classes
     */
    abstract public function getEntity();

    /**
     * Get the notifiable entity
     * Default: return entity itself
     */
    public function getNotifiable()
    {
        return $this->getEntity();
    }

    /**
     * Default metadata implementation
     */
    public function getMetadata(): array
    {
        return [];
    }

    /**
     * Default channels implementation
     */
    public function getChannels(): array
    {
        return config("notification.channels", ['database']);
    }

    /**
     * Get the serialized data
     *
     * @return array
     */
    public function getSerializedData(): array
    {
        return [];
    }

    /**
     * Get the notifiable client
     *
     * @return string|null
     */
    public function getNotifiableClientName(): ?string
    {
        return null;
    }
}
