<?php

namespace App\Core\Services;

use App\Core\Models\ScheduledEvent;
use App\Core\Contracts\ConfigurableListenerInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

/**
 * Configurable Event Dispatcher
 *
 * This service routes events to appropriate handlers based on configuration:
 * - sync: Process immediately
 * - queue: Dispatch to queue workers
 * - scheduled: Store for batch processing
 */
class EventDispatcher
{
    /**
     * Dispatch an event with configuration-driven routing
     *
     * @param object $event
     * @param string|null $eventKey Configuration key for this event
     * @return mixed
     */
    public function dispatch(object $event, ?string $eventKey = null)
    {
        // Check if event system is enabled
        if (!config('events.enabled', true)) {
            $this->logDisabled($event);
            return null;
        }

        $mode = $this->resolveProcessingMode($eventKey);

        $this->logDispatch($event, $eventKey, $mode);

        return match ($mode) {
            'sync' => $this->dispatchSync($event),
            'queue' => $this->dispatchQueue($event),
            'scheduled' => $this->dispatchScheduled($event, $eventKey),
            default => $this->dispatchSync($event),
        };
    }

    /**
     * Dispatch event synchronously (immediate processing)
     *
     * @param object $event
     * @return mixed
     */
    protected function dispatchSync(object $event)
    {
        return Event::dispatch($event);
    }

    /**
     * Dispatch event to queue
     *
     * @param object $event
     * @return mixed
     */
    protected function dispatchQueue(object $event)
    {
        // Laravel's Event::dispatch automatically queues listeners that implement ShouldQueue
        return Event::dispatch($event);
    }

    /**
     * Store event for scheduled processing
     *
     * @param object $event
     * @param string|null $eventKey
     * @return ScheduledEvent
     */
    protected function dispatchScheduled(object $event, ?string $eventKey): ScheduledEvent
    {
        $eventClass = get_class($event);
        $config = $eventKey ? config("events.events.{$eventKey}", []) : [];

        $scheduledEvent = ScheduledEvent::create([
            'event_class' => $eventClass,
            'event_data' => serialize($event),
            'frequency' => $config['frequency'] ?? config('events.scheduled.frequency'),
            'priority' => $config['priority'] ?? 'medium',
            'status' => 'pending',
            'max_attempts' => $config['tries'] ?? config('events.queue.tries', 3),
            'scheduled_at' => $this->calculateScheduledTime($config),
            'metadata' => [
                'dispatched_at' => now()->toISOString(),
                'dispatched_by' => auth()->id(),
                'environment' => app()->environment(),
            ],
        ]);

        $this->logScheduled($event, $scheduledEvent);

        return $scheduledEvent;
    }

    /**
     * Resolve processing mode for an event
     *
     * @param string|null $eventKey
     * @return string
     */
    protected function resolveProcessingMode(?string $eventKey): string
    {
        // Check event-specific mode
        if ($eventKey) {
            $eventMode = config("events.events.{$eventKey}.mode");
            if ($eventMode) {
                return $eventMode;
            }
        }

        // Check environment-specific mode
        $env = app()->environment();
        $envMode = config("events.mode_overrides.{$env}");
        if ($envMode) {
            return $envMode;
        }

        // Fall back to default mode
        return config('events.default_mode', 'queue');
    }

    /**
     * Calculate scheduled time based on frequency
     *
     * @param array $config
     * @return \Carbon\Carbon
     */
    protected function calculateScheduledTime(array $config): \Carbon\Carbon
    {
        $frequency = $config['frequency'] ?? null;

        if (!$frequency) {
            return now(); // Process immediately
        }

        // Parse frequency
        return match ($frequency) {
            'immediate' => now(),
            'hourly' => now()->addHour(),
            'daily' => now()->addDay(),
            'weekly' => now()->addWeek(),
            default => now()->addMinutes(5), // Default to 5 minutes
        };
    }

    /**
     * Log event dispatch
     *
     * @param object $event
     * @param string|null $eventKey
     * @param string $mode
     * @return void
     */
    protected function logDispatch(object $event, ?string $eventKey, string $mode): void
    {
        if (!config('events.monitoring.log_dispatched', false)) {
            return;
        }

        Log::info('[EventDispatcher] Event dispatched', [
            'event_class' => get_class($event),
            'event_key' => $eventKey,
            'mode' => $mode,
            'environment' => app()->environment(),
        ]);
    }

    /**
     * Log scheduled event
     *
     * @param object $event
     * @param ScheduledEvent $scheduledEvent
     * @return void
     */
    protected function logScheduled(object $event, ScheduledEvent $scheduledEvent): void
    {
        if (!config('events.monitoring.log_dispatched', false)) {
            return;
        }

        Log::info('[EventDispatcher] Event scheduled', [
            'event_class' => get_class($event),
            'scheduled_event_id' => $scheduledEvent->id,
            'scheduled_at' => $scheduledEvent->scheduled_at,
            'priority' => $scheduledEvent->priority,
        ]);
    }

    /**
     * Log disabled event system
     *
     * @param object $event
     * @return void
     */
    protected function logDisabled(object $event): void
    {
        Log::debug('[EventDispatcher] Event system is disabled', [
            'event_class' => get_class($event),
        ]);
    }
}

