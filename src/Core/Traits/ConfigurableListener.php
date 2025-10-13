<?php

namespace App\Core\Traits;

/**
 * Trait for listeners that support configuration-driven execution modes
 *
 * This trait provides default implementations for ConfigurableListenerInterface
 * by reading from the events configuration file.
 */
trait ConfigurableListener
{
    /**
     * Get the event configuration key for this listener
     *
     * @return string|null
     */
    protected function getEventConfigKey(): ?string
    {
        return $this->eventConfigKey ?? null;
    }

    /**
     * Get the listener configuration key
     *
     * @return string|null
     */
    protected function getListenerConfigKey(): ?string
    {
        return $this->listenerConfigKey ?? null;
    }

    /**
     * Determine if this listener should be queued
     *
     * @return bool
     */
    public function shouldQueue(): bool
    {
        $mode = $this->getProcessingMode();
        return $mode === 'queue';
    }

    /**
     * Determine if this listener should be scheduled
     *
     * @return bool
     */
    public function shouldSchedule(): bool
    {
        $mode = $this->getProcessingMode();
        return $mode === 'scheduled';
    }

    /**
     * Get the processing mode for this listener
     *
     * @return string
     */
    protected function getProcessingMode(): string
    {
        // Check listener-specific mode
        $listenerMode = $this->getListenerConfig('mode');
        if ($listenerMode) {
            return $listenerMode;
        }

        // Check event-specific mode
        $eventMode = $this->getEventConfig('mode');
        if ($eventMode) {
            return $eventMode;
        }

        // Check environment-specific mode
        $env = app()->environment();
        $envMode = config("events.mode_overrides.{$env}");
        if ($envMode) {
            return $envMode;
        }

        // Fall back to global processing mode
        return config('events.processing.mode', 'sync');
    }

    /**
     * Get the queue connection name
     *
     * @return string|null
     */
    public function getQueueConnection(): ?string
    {
        return $this->getListenerConfig('connection')
            ?? $this->getEventConfig('connection')
            ?? config('events.processing.queue_connection');
    }

    /**
     * Get the queue name
     *
     * @return string|null
     */
    public function getQueue(): ?string
    {
        // Check listener-specific queue
        $queue = $this->getListenerConfig('queue');
        if ($queue) {
            return $queue;
        }

        // Check event-specific queue
        $queue = $this->getEventConfig('queue');
        if ($queue) {
            return $queue;
        }

        // Check priority-based queue
        $priority = $this->getEventConfig('priority', 'medium');
        $priorityQueue = config("events.priority_queues.{$priority}");
        if ($priorityQueue) {
            return $priorityQueue;
        }

        // Fall back to default queue
        return config('events.processing.queue_name', 'events');
    }

    /**
     * Get the number of times the listener may be attempted
     *
     * @return int
     */
    public function getTries(): int
    {
        return $this->getListenerConfig('tries')
            ?? $this->getEventConfig('tries')
            ?? config('events.processing.max_tries', 3);
    }

    /**
     * Get the number of seconds to wait before retrying
     *
     * @return array|int
     */
    public function getBackoff(): array|int
    {
        $backoff = $this->getListenerConfig('backoff')
            ?? $this->getEventConfig('backoff')
            ?? config('events.processing.backoff');

        // Parse comma-separated string to array
        if (is_string($backoff)) {
            return array_map('intval', explode(',', $backoff));
        }

        return $backoff ?? 30;
    }

    /**
     * Get the maximum number of seconds the listener can run
     *
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->getListenerConfig('timeout')
            ?? $this->getEventConfig('timeout')
            ?? config('events.processing.timeout_seconds', 60);
    }

    /**
     * Get the schedule frequency for scheduled listeners
     *
     * @return string|null
     */
    public function getScheduleFrequency(): ?string
    {
        return $this->getListenerConfig('frequency')
            ?? $this->getEventConfig('frequency')
            ?? config('events.scheduled.frequency');
    }

    /**
     * Get event configuration value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getEventConfig(string $key, $default = null)
    {
        $eventKey = $this->getEventConfigKey();
        if (!$eventKey) {
            return $default;
        }

        return config("events.events.{$eventKey}.{$key}", $default);
    }

    /**
     * Get listener configuration value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getListenerConfig(string $key, $default = null)
    {
        $eventKey = $this->getEventConfigKey();
        $listenerKey = $this->getListenerConfigKey();

        if (!$eventKey || !$listenerKey) {
            return $default;
        }

        return config("events.events.{$eventKey}.listeners.{$listenerKey}.{$key}", $default);
    }
}

