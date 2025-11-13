<?php

namespace App\Modules\Notification\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Modules\Notification\Database\Models\NotificationOutbox;
use App\Modules\Notification\Database\Models\Notification as NotificationModel;
use App\Modules\Notification\Services\NotificationService;

class ProcessNotificationOutbox extends Command
{
    /**
     * The console command signature
     */
    protected $signature = 'notifications:outbox:process {--limit=100}';

    /**
     * The console command description
     */
    protected $description = 'Process pending notification outbox rows';

    /**
     * Handle the console command
     * @return int
     */
    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $rows = NotificationOutbox::query()
            ->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('available_at')->orWhere('available_at', '<=', now());
            })
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        foreach ($rows as $row) {
            DB::transaction(function () use ($row) {
                $row->status = 'processing';
                $row->attempts++;
                $row->save();

                Log::info('NotifTrace', [
                    'stage' => 'outbox_processing_start',
                    'outbox_id' => $row->id,
                    'event_type' => $row->event_type,
                    'notifiable_type' => $row->notifiable_type,
                    'notifiable_id' => $row->notifiable_id,
                    'channels' => $row->channels,
                ]);

                $channels = $row->channels ?? [];
                $payload = $row->payload ?? [];

                // 1) Create database notification synchronously if requested
                if (in_array('database', $channels, true)) {
                    $toDatabase = $payload['to_database'] ?? [];
                    $dbNotification = NotificationModel::create([
                        'id' => Str::uuid(),
                        'type' => \App\Modules\Notification\Notifications\EntityEventNotification::class,
                        'notifiable_type' => $row->notifiable_type, // alias like 'user'
                        'notifiable_id' => $row->notifiable_id,
                        'data' => $toDatabase,
                        'read_at' => null,
                    ]);

                    Log::info('NotifTrace', [
                        'stage' => 'db_notification_created',
                        'outbox_id' => $row->id,
                        'notification_id' => $dbNotification->id,
                    ]);
                }

                // 2) Queue email (if requested)
                if (in_array('mail', $channels, true)) {
                    $notifiable = $this->resolveNotifiable($row->notifiable_type, $row->notifiable_id);
                    if ($notifiable) {
                        $entity = $row->entity_type ? $this->safeFind($row->entity_type, $row->entity_id) : $notifiable;
                        $notification = new \App\Modules\Notification\Notifications\EntityEventNotification(
                            $entity,
                            $row->event_type,
                            $payload['mail_data'] ?? [],
                            ['mail']
                        );
                        app(NotificationService::class)->send($notifiable, $notification);

                        Log::info('NotifTrace', [
                            'stage' => 'mail_queued',
                            'outbox_id' => $row->id,
                            'notifiable_type' => $row->notifiable_type,
                            'notifiable_id' => $row->notifiable_id,
                        ]);
                    }
                }

                $row->status = 'sent';
                $row->save();

                Log::info('NotifTrace', [
                    'stage' => 'outbox_processed',
                    'outbox_id' => $row->id,
                    'status' => $row->status,
                ]);
            }, 1);
        }

        $this->info("Processed {$rows->count()} outbox rows.");
        return Command::SUCCESS;
    }

    /**
     * Resolve the notifiable
     * @param string $morphAliasOrClass
     * @param mixed $id
     * @return mixed
     */
    protected function resolveNotifiable(string $morphAliasOrClass, $id)
    {
        $map = Relation::morphMap() ?? [];
        $class = $map[$morphAliasOrClass] ?? $morphAliasOrClass;
        return $this->safeFind($class, $id);
    }

    /**
     * Safe find an entity
     * @param string $class
     * @param mixed $id
     * @return mixed
     * @throws \Throwable
     */
    protected function safeFind(string $class, $id)
    {
        try {
            return app($class)->find($id);
        } catch (\Throwable $e) {
            Log::warning('Outbox resolve entity failed', ['class' => $class, 'id' => $id, 'error' => $e->getMessage()]);
            return null;
        }
    }
}


