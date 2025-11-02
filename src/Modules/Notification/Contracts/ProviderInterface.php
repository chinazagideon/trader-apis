<?php

namespace App\Modules\Notification\Contracts;

/**
 * Universal provider interface for all notification channels
 */
interface ProviderInterface
{
    /**
     * Send notification via this provider
     *
     * @param mixed $notifiable
     * @param array $data
     * @return array Result with 'success' boolean and optional 'message'/'response'
     */
    public function send($notifiable, array $data): array;

    /**
     * Check if provider is available/configured
     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * Get provider health status
     *
     * @return array ['status' => 'healthy'|'degraded'|'down', 'message' => '...']
     */
    public function getHealth(): array;

    /**
     * Get provider name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get provider type/channel
     *
     * @return string
     */
    public function getType(): string;
}

