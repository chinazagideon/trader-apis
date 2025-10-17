<?php

namespace App\Modules\Swap\Database\Models;

use App\Core\Models\CoreModel;
use App\Core\Traits\HasTimestamps;
use App\Core\Traits\HasUuid;

class SwapRateHistory extends CoreModel
{
    use HasTimestamps, HasUuid;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'from_currency_id',
        'to_currency_id',
        'rate',
        'spread',
        'source',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'rate' => 'decimal:2',
            'spread' => 'decimal:2',
        ];
    }
}

