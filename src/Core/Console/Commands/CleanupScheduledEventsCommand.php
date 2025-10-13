<?php

namespace App\Core\Console\Commands;

use App\Core\Models\ScheduledEvent;
use Illuminate\Console\Command;

class CleanupScheduledEventsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'events:cleanup-scheduled
                            {--days= : Delete events older than N days (default from config)}
                            {--status= : Cleanup specific status (processed, failed, all)}
                            {--dry-run : Preview cleanup without deleting}';

    /**
     * The console command description.
     */
    protected $description = 'Cleanup old scheduled events from the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = $this->option('days') ?? config('events.scheduled.max_age_days', 7);
        $status = $this->option('status') ?? 'processed';
        $dryRun = $this->option('dry-run');

        $this->info("Cleaning up scheduled events older than {$days} days...");
        $this->info('');

        // Build query
        $query = ScheduledEvent::olderThan($days);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info('No events found for cleanup.');
            return 0;
        }

        if ($dryRun) {
            $this->info("Would delete {$count} event(s)");
            $this->displayEvents($query->limit(10)->get());
            return 0;
        }

        if (!$this->confirm("Are you sure you want to delete {$count} event(s)?")) {
            $this->info('Cleanup cancelled.');
            return 0;
        }

        $deleted = $query->delete();

        $this->info('');
        $this->info("Successfully deleted {$deleted} event(s)");

        return 0;
    }

    /**
     * Display sample events
     */
    protected function displayEvents(\Illuminate\Database\Eloquent\Collection $events): void
    {
        $this->info('Sample events to be deleted (first 10):');
        $this->table(
            ['ID', 'Event Class', 'Status', 'Created At', 'Age (days)'],
            $events->map(function ($event) {
                return [
                    $event->id,
                    class_basename($event->event_class),
                    $event->status,
                    $event->created_at->format('Y-m-d H:i:s'),
                    $event->created_at->diffInDays(now()),
                ];
            })
        );
    }
}

