<?php

namespace App\Modules\Notification\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NotificationOutbox extends Model
{
    protected $table = 'notification_outbox';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'event_type',
        'notifiable_type',
        'notifiable_id',
        'entity_type',
        'entity_id',
        'channels',
        'payload',
        'status',
        'attempts',
        'available_at',
        'dedupe_key',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'channels' => 'array',
        'payload' => 'array',
        'available_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}



