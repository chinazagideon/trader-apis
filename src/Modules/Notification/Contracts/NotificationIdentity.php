<?php

namespace App\Modules\Notification\Contracts;

/**
 * Value object for notification identity
 */
class NotificationIdentity
{
    public function __construct(
        public readonly ?string $fromName = null,
        public readonly ?string $fromEmail = null,
        public readonly ?string $fromPhone = null,
        public readonly ?string $replyToEmail = null,
        public readonly ?string $replyToName = null,
        public readonly ?string $replyToPhone = null,
        public readonly array $metadata = [],
    ) {}

    /**
     * Merge with another identity (latter takes precedence)
     */
    public function merge(?NotificationIdentity $other): NotificationIdentity
    {
        if (!$other) {
            return $this;
        }

        return new NotificationIdentity(
            fromName: $other->fromName ?? $this->fromName,
            fromEmail: $other->fromEmail ?? $this->fromEmail,
            fromPhone: $other->fromPhone ?? $this->fromPhone,
            replyToEmail: $other->replyToEmail ?? $this->replyToEmail,
            replyToName: $other->replyToName ?? $this->replyToName,
            replyToPhone: $other->replyToPhone ?? $this->replyToPhone,
            metadata: array_merge($this->metadata, $other->metadata),
        );
    }
}
