<?php

namespace App\Modules\Notification\Contracts;

interface ProviderResolverInterface
{
    /**
     * Resolve a provider instance by name and config
     *
     * @param string $name Provider name (e.g., 'sendgrid', 'smtp')
     * @param array $config Provider configuration
     * @param string $channel Channel type (e.g., 'mail', 'sms')
     * @return ProviderInterface|null
     */
    public function resolve(string $name, array $config, string $channel): ?ProviderInterface;
}
