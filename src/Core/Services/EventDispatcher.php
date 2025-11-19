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

        $hasListeners = Event::hasListeners(get_class($event));
        Log::info('[EventDispatcher] Dispatching event', [
            'event_class' => get_class($event),
            'event_key' => $eventKey,
            'mode' => $mode,
            'has_listeners' => $hasListeners,
        ]);
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
        Log::info('[EventDispatcher] Dispatching synchronously', [
            'event_class' => get_class($event),
        ]);

        $result = Event::dispatch($event);

        Log::info('[EventDispatcher] Event dispatched synchronously', [
            'event_class' => get_class($event),
            'result' => $result,
        ]);

        return $result;
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
        Log::info('[EventDispatcher] Dispatching to queue', [
            'event_class' => get_class($event),
            'listeners_implement_shouldqueue' => $this->checkListenersShouldQueue($event),
            'implements_should_dispatch_after_commit' => $event instanceof \Illuminate\Contracts\Events\ShouldDispatchAfterCommit,
        ]);

        // Use Event::dispatch which respects ShouldDispatchAfterCommit and queues ShouldQueue listeners
        $result = Event::dispatch($event);

        // After dispatch, check if jobs were actually queued
        $listeners = Event::getListeners(get_class($event));
        $queuedJobs = [];
        foreach ($listeners as $listener) {
            if (is_string($listener) && class_exists($listener)) {
                $reflection = new \ReflectionClass($listener);
                if ($reflection->implementsInterface(\Illuminate\Contracts\Queue\ShouldQueue::class)) {
                    $queuedJobs[] = $listener;
                }
            }
        }

        Log::info('[EventDispatcher] Event dispatched to queue', [
            'event_class' => get_class($event),
            'result' => $result,
            'listeners_that_should_queue' => $queuedJobs,
            'queue_connection' => config('queue.default'),
        ]);

        return $result;
    }

    /**
     * Check if any listeners implement ShouldQueue
     */
    protected function checkListenersShouldQueue(object $event): array
    {
        $listeners = Event::getListeners(get_class($event));
        $info = [];

        foreach ($listeners as $listener) {
            $className = null;
            if (is_string($listener)) {
                $className = $listener;
            } elseif (is_array($listener) && isset($listener[0])) {
                $className = is_object($listener[0]) ? get_class($listener[0]) : (string)$listener[0];
            } elseif (is_object($listener)) {
                // Handle already-instantiated listener objects
                $className = get_class($listener);
            }

            if ($className && class_exists($className)) {
                $implementsShouldQueue = in_array(
                    \Illuminate\Contracts\Queue\ShouldQueue::class,
                    class_implements($className)
                );
                $info[] = [
                    'class' => $className,
                    'implements_shouldqueue' => $implementsShouldQueue,
                ];
            } else {
                $info[] = [
                    'class' => gettype($listener),
                    'implements_shouldqueue' => false,
                ];
            }
        }

        return $info;
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
                'dispatched_by' => 0,
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

