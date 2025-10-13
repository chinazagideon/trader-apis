<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledEvent extends Model
{
    protected $table = 'scheduled_events';

    protected $fillable = [
        'event_class',
        'event_data',
        'listener_class',
        'frequency',
        'priority',
        'status',
        'attempts',
        'max_attempts',
        'scheduled_at',
        'processed_at',
        'failed_at',
        'error_message',
        'error_trace',
        'metadata',
    ];

    protected $casts = [
        'event_data' => 'array',
        'metadata' => 'array',
        'scheduled_at' => 'datetime',
        'processed_at' => 'datetime',
        'failed_at' => 'datetime',
        'attempts' => 'integer',
        'max_attempts' => 'integer',
    ];

    /**
     * Scope to get pending events
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            });
    }

    /**
     * Scope to get failed events
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to get processed events
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    /**
     * Scope to get events by priority
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to get old events for cleanup
     */
    public function scopeOlderThan($query, int $days)
    {
        return $query->where('created_at', '<', now()->subDays($days));
    }

    /**
     * Mark event as processing
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'attempts' => $this->attempts + 1,
        ]);
    }

    /**
     * Mark event as processed
     */
    public function markAsProcessed(): void
    {
        $this->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark event as failed
     */
    public function markAsFailed(\Throwable $exception): void
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => $exception->getMessage(),
            'error_trace' => $exception->getTraceAsString(),
        ]);
    }

    /**
     * Check if event can be retried
     */
    public function canRetry(): bool
    {
        return $this->attempts < $this->max_attempts;
    }

    /**
     * Reset for retry
     */
    public function resetForRetry(): void
    {
        if ($this->canRetry()) {
            $this->update([
                'status' => 'pending',
                'scheduled_at' => now()->addMinutes(5), // Delay retry by 5 minutes
            ]);
        }
    }

    /**
     * Deserialize event instance
     */
    public function getEventInstance()
    {
        $eventClass = $this->event_class;

        if (!class_exists($eventClass)) {
            throw new \RuntimeException("Event class {$eventClass} does not exist");
        }

        // Reconstruct event from serialized data
        return unserialize($this->event_data);
    }
}

