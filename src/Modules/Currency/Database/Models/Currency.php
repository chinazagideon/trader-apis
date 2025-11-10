<?php

namespace App\Modules\Currency\Database\Models;

use App\Core\Models\CoreModel;
use App\Core\Traits\HasTimestamps;
use App\Core\Traits\HasUuid;

class Currency extends CoreModel
{
    use HasTimestamps, HasUuid;

    /**
     * The table associated with the model.
     */
    protected $table = 'currencies';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'symbol',
        'code',
        'type',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'type' => 'string',
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Scope for fiat currencies.
     */
    public function scopeFiat($query)
    {
        return $query->where('type', 'fiat');
    }

    /**
     * Scope for crypto currencies.
     */
    public function scopeCrypto($query)
    {
        return $query->where('type', 'crypto');
    }

    /**
     * Scope for active currencies.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for inactive currencies.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}
