<?php

namespace App\Core\Console\Commands;

use App\Core\Models\ScheduledEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessScheduledEventsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'events:process-scheduled
                            {--limit=100 : Maximum number of events to process}
                            {--priority= : Process specific priority (critical, high, medium, low)}
                            {--dry-run : Preview events without processing}';

    /**
     * The console command description.
     */
    protected $description = 'Process scheduled events in batches';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!config('events.scheduled.enabled', true)) {
            $this->info('Scheduled event processing is disabled.');
            return 0;
        }

        $limit = (int) $this->option('limit');
        $priority = $this->option('priority');
        $dryRun = $this->option('dry-run');

        $this->info('Processing scheduled events...');
        $this->info('');

        // Get pending events
        $events = $this->getPendingEvents($limit, $priority);

        if ($events->isEmpty()) {
            $this->info('No pending scheduled events to process.');
            return 0;
        }

        $this->info("Found {$events->count()} pending event(s)");
        $this->info('');

        if ($dryRun) {
            $this->displayEvents($events);
            return 0;
        }

        // Process events
        $processed = 0;
        $failed = 0;

        $progressBar = $this->output->createProgressBar($events->count());
        $progressBar->start();

        foreach ($events as $scheduledEvent) {
            try {
                $this->processEvent($scheduledEvent);
                $processed++;
            } catch (Throwable $e) {
                $failed++;
                $this->handleFailure($scheduledEvent, $e);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info('');
        $this->info('');

        // Display summary
        $this->displaySummary($processed, $failed);

        return $failed > 0 ? 1 : 0;
    }

    /**
     * Get pending events to process
     */
    protected function getPendingEvents(int $limit, ?string $priority): \Illuminate\Database\Eloquent\Collection
    {
        $query = ScheduledEvent::pending()
            ->orderBy('priority', 'desc')
            ->orderBy('scheduled_at', 'asc')
            ->limit($limit);

        if ($priority) {
            $query->byPriority($priority);
        }

        return $query->get();
    }

    /**
     * Process a single scheduled event
     */
    protected function processEvent(ScheduledEvent $scheduledEvent): void
    {
        $scheduledEvent->markAsProcessing();

        try {
            // Deserialize and dispatch the event
            $event = $scheduledEvent->getEventInstance();
            Event::dispatch($event);

            $scheduledEvent->markAsProcessed();

            Log::info('[ProcessScheduledEvents] Event processed successfully', [
                'scheduled_event_id' => $scheduledEvent->id,
                'event_class' => $scheduledEvent->event_class,
                'attempts' => $scheduledEvent->attempts,
            ]);
        } catch (Throwable $e) {
            throw $e; // Re-throw to be caught by handle()
        }
    }

    /**
     * Handle event processing failure
     */
    protected function handleFailure(ScheduledEvent $scheduledEvent, Throwable $exception): void
    {
        Log::error('[ProcessScheduledEvents] Event processing failed', [
            'scheduled_event_id' => $scheduledEvent->id,
            'event_class' => $scheduledEvent->event_class,
            'attempts' => $scheduledEvent->attempts,
            'error' => $exception->getMessage(),
        ]);

        if ($scheduledEvent->canRetry()) {
            $scheduledEvent->resetForRetry();
        } else {
            $scheduledEvent->markAsFailed($exception);
        }
    }

    /**
     * Display events in dry-run mode
     */
    protected function displayEvents(\Illuminate\Database\Eloquent\Collection $events): void
    {
        $this->table(
            ['ID', 'Event Class', 'Priority', 'Scheduled At', 'Attempts'],
            $events->map(function ($event) {
                return [
                    $event->id,
                    class_basename($event->event_class),
                    $event->priority,
                    $event->scheduled_at?->format('Y-m-d H:i:s') ?? 'Now',
                    "{$event->attempts}/{$event->max_attempts}",
                ];
            })
        );
    }

    /**
     * Display processing summary
     */
    protected function displaySummary(int $processed, int $failed): void
    {
        $this->info('Processing Summary');
        $this->info('═══════════════════════════════════════');
        $this->line("  <info>Processed Successfully:</info> {$processed}");

        if ($failed > 0) {
            $this->line("  <error>Failed:</error> {$failed}");
        } else {
            $this->line("  <info>Failed:</info> {$failed}");
        }

        $this->info('═══════════════════════════════════════');
    }
}

