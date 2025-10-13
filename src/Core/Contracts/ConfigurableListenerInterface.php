<?php

namespace App\Core\Contracts;

/**
 * Interface for listeners that can be configured to run in different modes
 *
 * This interface allows listeners to dynamically determine their execution mode
 * (sync, queue, scheduled) based on configuration and environment.
 */
interface ConfigurableListenerInterface
{
    /**
     * Determine if this listener should be queued
     *
     * @return bool
     */
    public function shouldQueue(): bool;

    /**
     * Determine if this listener should be scheduled for batch processing
     *
     * @return bool
     */
    public function shouldSchedule(): bool;

    /**
     * Get the queue connection name
     *
     * @return string|null
     */
    public function getQueueConnection(): ?string;

    /**
     * Get the queue name
     *
     * @return string|null
     */
    public function getQueue(): ?string;

    /**
     * Get the number of times the listener may be attempted
     *
     * @return int
     */
    public function getTries(): int;

    /**
     * Get the number of seconds to wait before retrying
     *
     * @return array|int
     */
    public function getBackoff(): array|int;

    /**
     * Get the maximum number of seconds the listener can run
     *
     * @return int
     */
    public function getTimeout(): int;

    /**
     * Get the schedule frequency for scheduled listeners
     *
     * @return string|null
     */
    public function getScheduleFrequency(): ?string;
}

