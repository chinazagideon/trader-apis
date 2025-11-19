<?php

namespace App\Core\Contracts;

interface NotificationEventsContract
{
    /**
     * Get the entity associated with this event
     *
     * @return mixed The entity (User, Investment, Transaction, etc.)
     */
    public function getEntity();

    /**
     * Get the notifiable entity (who should receive the notification)
     *
     * @return mixed The notifiable entity
     */
    public function getNotifiable();

    /**
     * Get the metadata
     *
     * @return array
     */
    public function getMetadata(): array;

    /**
     * Get notification channels
     *
     * @return array
     */
    public function getChannels(): array;

    /**
     * Get the title
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Get the message
     *
     * @return string
     */
    public function getMessage(): string;

    /**
     * Get the action url
     *
     * @return string
     */
    public function getActionUrl(): string;

    /**
     * Get the action text
     *
     * @return string
     */
    public function getActionText(): string;

    /**
     * Get the event type key for template lookup
     *
     * @return string e.g., 'user_was_created', 'investment_created'
     */
    public function getEventType(): string;

    /**
     * Get the notifiable client
     *
     * @return array|null
     */
    public function getNotifiableClientName(): ?string;
}
