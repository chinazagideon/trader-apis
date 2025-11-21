<?php

namespace App\Modules\Notification\Services;

use App\Modules\Notification\Contracts\ProviderInterface;
use App\Modules\Notification\Contracts\ProviderResolverInterface;
use Illuminate\Support\Facades\Log;

class ProviderResolver implements ProviderResolverInterface
{
    /**
     * Resolved providers
     *
     * @var array
     */
    protected array $resolvedProviders = [];

    /**
     * Resolve a provider instance by name and config
     *
     * @param string $name Provider name (e.g., 'sendgrid', 'smtp')
     * @param array $config Provider configuration
     * @param string $channel Channel type (e.g., 'mail', 'sms')
     * @return ProviderInterface|null
     */
    public function resolve(string $name, array $config, string $channel): ?ProviderInterface
    {
        $cacheKey = "{$channel}:{$name}";

        // Return cached instance if available
        if (isset($this->resolvedProviders[$cacheKey])) {
            return $this->resolvedProviders[$cacheKey];
        }

        $provider = $this->createProvider($name, $config, $channel);

        if ($provider) {
            $this->resolvedProviders[$cacheKey] = $provider;
        }

        return $provider;
    }

    /**
     * Create a provider instance by name and config
     *
     * @param string $name Provider name (e.g., 'sendgrid', 'smtp')
     * @param array $config Provider configuration
     * @param string $channel Channel type (e.g., 'mail', 'sms')
     * @return ProviderInterface|null
     */
    protected function createProvider(string $name, array $config, string $channel): ?ProviderInterface
    {
        // Try to resolve from service container first (for registered providers)
        $serviceClass = $this->getProviderServiceClass($name, $channel);

        if ($serviceClass && class_exists($serviceClass)) {
            try {
                $provider = app($serviceClass);
                if ($provider instanceof ProviderInterface) {
                    return $provider;
                }
            } catch (\Exception $e) {
                Log::warning("Failed to resolve provider from container", [
                    'name' => $name,
                    'channel' => $channel,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        return null;
    }

    /**
     * Get the provider service class by name and channel
     *
     * @param string $name Provider name (e.g., 'sendgrid', 'smtp')
     * @param string $channel Channel type (e.g., 'mail', 'sms')
     * @return string|null
     */
    protected function getProviderServiceClass(string $name, string $channel): ?string
    {
        // Map provider names to their service classes
        $providerMap = [
            'mail' => [
                'sendgrid' => \App\Modules\Notification\Services\SendGridMailerService::class,
                'smtp' => \App\Modules\Notification\Services\SMTPMailerService::class,
                'default' => \App\Modules\Notification\Services\LaravelMailService::class,
                'log' => \App\Modules\Notification\Services\LaravelMailService::class,
            ],
            'sms' => [],
        ];

        return $providerMap[$channel][$name] ?? null;
    }
}
