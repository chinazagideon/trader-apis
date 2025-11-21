<?php

namespace App\Modules\Notification\Contracts;

/**
 * Contract for resolving notification identity information
 *
 * This allows any module (Client, Tenant, Organization, etc.) to provide
 * identity information without coupling Notification to specific implementations.
 */
interface NotificationIdentityResolverInterface
{
    /**
     * Resolve identity information for a notification channel
     *
     * @param string $channel The notification channel (mail, sms, push, etc.)
     * @param array $context Optional context (e.g., ['entity_type' => 'user', 'entity_id' => 123])
     * @return NotificationIdentity|null Identity information or null if not available
     */
    public function resolve(string $channel, array $context = []): ?NotificationIdentity;

    
    /**
     * Extract client_id from a model using multiple methods
     */
    public function extractClientId($model): ?int;
}
