<?php

namespace App\Modules\Notification\Services;

use App\Modules\Notification\Contracts\NotificationIdentityResolverInterface;
use App\Modules\Notification\Contracts\NotificationIdentity;

/**
 * Default implementation that returns null (no identity override)
 * This allows the system to work even if no identity resolver is provided
 */
class NullNotificationIdentityResolver implements NotificationIdentityResolverInterface
{
    public function resolve(string $channel, array $context = []): ?NotificationIdentity
    {
        return null; // No identity override
    }

    public function extractClientId($model): ?int
    {
        return null;
    }
}
