<?php

namespace App\Modules\Notification\Database\Models;

use App\Core\Traits\HasTimestamps;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class NotificationConfig extends Model
{
    use HasTimestamps, HasUuid;

    protected $fillable = [
        'uuid',
        'type',
        'name',
        'channel',
        'config',
        'priority',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_active' => 'boolean',
            'priority' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Scope to get active configs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get configs by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get configs by channel
     */
    public function scopeByChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope to get providers ordered by priority
     */
    public function scopeOrderedByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    /**
     * Scope to get email providers
     */
    public function scopeEmailProviders($query)
    {
        return $query->where('type', 'email_provider');
    }

    /**
     * Scope to get SMS providers
     */
    public function scopeSmsProviders($query)
    {
        return $query->where('type', 'sms_provider');
    }

    /**
     * Scope to get push providers
     */
    public function scopePushProviders($query)
    {
        return $query->where('type', 'push_provider');
    }

    /**
     * Scope to get templates
     */
    public function scopeTemplates($query)
    {
        return $query->where('type', 'template');
    }
}

