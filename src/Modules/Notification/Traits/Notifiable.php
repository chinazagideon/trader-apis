<?php

namespace App\Modules\Notification\Traits;

use App\Modules\Notification\Database\Models\Notification;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\Notifiable as LaravelNotifiable;

/**
 * Trait to make entities notifiable with custom notification model
 * Add this trait to any model that should receive notifications
 *
 * This trait extends Laravel's Notifiable trait and adds custom notification relationships
 * that use our custom Notification model instead of Laravel's default one.
 */
trait Notifiable
{
    use LaravelNotifiable;

    /**
     * Get all of the entity's custom notifications.
     */
    public function customNotifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get the entity's unread custom notifications.
     */
    public function unreadCustomNotifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')
                    ->whereNull('read_at')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get the entity's read custom notifications.
     */
    public function readCustomNotifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')
                    ->whereNotNull('read_at')
                    ->orderBy('created_at', 'desc');
    }
}

