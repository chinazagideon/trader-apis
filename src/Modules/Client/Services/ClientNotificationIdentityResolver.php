<?php

namespace App\Modules\Client\Services;

use App\Core\Traits\HasClientApp;
use App\Modules\Notification\Contracts\NotificationIdentityResolverInterface;
use App\Modules\Notification\Contracts\NotificationIdentity;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Modules\Client\Contracts\ClientRepositoryContract;

/**
 * Client-specific implementation of notification identity resolver
 *
 * Uses ClientSecret sub-model to retrieve notification identity configuration
 * This is the ONLY place where Client and Notification modules interact.
 */
class ClientNotificationIdentityResolver implements NotificationIdentityResolverInterface
{
    use HasClientApp;

    // Cache TTL: 1 hour (configurable)
    protected const CACHE_TTL = 3600;

    // Cache prefix
    protected const CACHE_PREFIX = 'client_notification_identity';

    public function __construct(
        private ClientSecretService $clientSecretService,
        private ClientRepositoryContract $clientRepository
    ) {}

    /**
     * Resolve notification identity from ClientSecret using action-based lookup
     *
     * Now uses action column: module_name='notification', action='mail' for mail identity
     */
    public function resolve(string $channel, array $context = []): ?NotificationIdentity
    {
        // Get client_id from multiple sources
        $clientId = $this->resolveClientId($context);


        if (!$clientId) {
            return null;
        }

        // Map channel to action
        $action = $this->getActionForChannel($channel);

        // Try cache first
        $cacheKey = $this->getCacheKey($clientId, $action);
        $cachedIdentity = Cache::get($cacheKey);

        if ($cachedIdentity instanceof NotificationIdentity) {
            return $cachedIdentity;
        }

        // Get secrets for notification module with specific action
        $secrets = $this->clientSecretService->getSecretsForClientModuleAndAction(
            $clientId,
            'notification',
            $action
        );

        if (!$secrets || !is_array($secrets)) {
            return null;
        }

        // Add client_id to secrets for personalization lookup
        $secrets['client_id'] = $clientId;

        // Build NotificationIdentity from secrets
        $identity = $this->buildIdentityFromSecrets($channel, $secrets);

        // Cache the result
        Cache::put($cacheKey, $identity, self::CACHE_TTL);

        return $identity;
    }

    /**
     * Map notification channel to action name
     */
    protected function getActionForChannel(string $channel): string
    {
        return match ($channel) {
            'mail' => 'mail',
            'sms' => 'sms',
            'push' => 'push',
            'slack' => 'slack',
            default => $channel,
        };
    }
    /**
     * Build NotificationIdentity from secrets data
     */
    protected function buildIdentityFromSecrets(string $channel, array $secrets): NotificationIdentity
    {
        // Normalize key names (support both snake_case and config-style)
        $normalized = $this->normalizeIdentityData($secrets);

        // Get client_id from secrets or context to fetch client config
        $clientId = $normalized['client_id'] ?? null;
        $personalization = $this->getClientPersonalization($clientId);

        // Merge personalization into metadata
        $metadata = array_merge(
            $normalized['metadata'] ?? [],
            $personalization
        );

        return new NotificationIdentity(
            fromName: $normalized['from_name'] ?? $normalized['MAIL_FROM_NAME'] ?? null,
            fromEmail: $normalized['from_email'] ?? $normalized['MAIL_FROM_ADDRESS'] ?? null,
            fromPhone: $normalized['from_phone'] ?? $normalized['from_number'] ?? null,
            replyToEmail: $normalized['reply_to_email'] ?? null,
            replyToName: $normalized['reply_to_name'] ?? null,
            replyToPhone: $normalized['reply_to_phone'] ?? null,
            metadata: $metadata,
        );
    }

    /**
     * Normalize identity data keys (support multiple naming conventions)
     */
    protected function normalizeIdentityData(array $data): array
    {
        $normalized = [];

        $mappings = [
            'MAIL_FROM_NAME' => 'from_name',
            'MAIL_FROM_ADDRESS' => 'from_email',
            'from_address' => 'from_email',
            'from_number' => 'from_phone',
            'sms_from' => 'from_phone',
        ];

        foreach ($data as $key => $value) {
            $normalizedKey = $mappings[$key] ?? $key;
            $normalized[$normalizedKey] = $value;
        }

        return array_merge($data, $normalized);
    }

    /**
     * Generate cache key
     */
    protected function getCacheKey(int $clientId, ?string $action): string
    {
        return sprintf('%s:%d:%s', self::CACHE_PREFIX, $clientId, $action ?? 'default');
    }

    /**
     * Clear cache for a client and action
     */
    public function clearCache(int $clientId, ?string $action = null): void
    {
        if ($action) {
            Cache::forget($this->getCacheKey($clientId, $action));
        } else {
            // Clear all actions for this client
            $actions = ['mail', 'sms', 'push', 'slack'];
            foreach ($actions as $act) {
                Cache::forget($this->getCacheKey($clientId, $act));
            }
        }
    }


    /**
     * Resolve client_id from multiple sources
     */
    protected function resolveClientId(array $context): ?int
    {
        // Priority 1: From container context (request-scoped)
        $clientId = $this->getClientId();
        if ($clientId) {
            return $clientId;
        }

        // Priority 2: From context array (passed explicitly)
        if (isset($context['client_id']) && is_int($context['client_id'])) {
            return $context['client_id'];
        }

        // Priority 3: From notifiable model
        if (isset($context['notifiable_type']) && isset($context['notifiable_id'])) {
            $notifiable = $this->resolveNotifiableFromContext($context);
            $clientId = $this->extractClientId($notifiable);
            if ($clientId) {
                return $clientId;
            }
        }

        return null;
    }

    /**
     * Resolve notifiable from context
     */
    protected function resolveNotifiableFromContext(array $context)
    {
        try {
            $notifiableType = $context['notifiable_type'] ?? null;
            $notifiableId = $context['notifiable_id'] ?? null;

            if (!$notifiableType || !$notifiableId) {
                return null;
            }

            // Handle morph map aliases
            $map = \Illuminate\Database\Eloquent\Relations\Relation::morphMap() ?? [];
            $class = $map[$notifiableType] ?? $notifiableType;

            if (!class_exists($class)) {
                return null;
            }

            $notifiable = app($class)->find($notifiableId);


            return $notifiable;
        } catch (\Exception $e) {
            Log::warning('Failed to resolve notifiable from context', [
                'context' => $context,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
    /**
     * Extract client_id from a model using multiple methods
     */
    public function extractClientId($model): ?int
    {
        if (!$model) {
            return null;
        }

        // Try direct attribute access (works with Eloquent magic __get) if it exists
        if (isset($model->client_id)) {
            return (int) $model->client_id;
        }

        // Try Eloquent getAttribute if it exists
        if (method_exists($model, 'getAttribute')) {
            $clientId = $model->getAttribute('client_id');
            if ($clientId !== null) {
                return (int) $clientId;
            }
        }

        // Try attributes array
        if (method_exists($model, 'getAttributes')) {
            $attributes = $model->getAttributes();
            if (isset($attributes['client_id'])) {
                return (int) $attributes['client_id'];
            }
        }

        return null;
    }

    /**
     * Get client personalization data (logo, footer, layout)
     *
     * @param int|null $clientId
     * @return array
     */
    protected function getClientPersonalization(?int $clientId): array
    {
        if (!$clientId) {
            return [];
        }

        try {
            $client = $this->clientRepository->findById($clientId);

            if (!$client || !$client->config) {
                return [];
            }

            $config = is_array($client->config) ? $client->config : (array) $client->config;

            return [
                'client_id' => $clientId,
                'client_name' => $client->name ?? null,
                'client_slug' => $client->slug ?? null,
                'client_logo' => $config['logo'] ?? $config['logo_url'] ?? null,
                'client_footer' => $config['footer'] ?? $config['email_footer'] ?? null,
                'client_layout' => $config['email_layout'] ?? $config['layout'] ?? 'default',
                'app_url' => $config['app_url'] ?? null,
                'app_name' => $config['app_name'] ?? $client->name ?? null,
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to fetch client personalization', [
                'client_id' => $clientId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
}
