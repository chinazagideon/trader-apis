<?php

namespace App\Modules\Swap\Database\Models;

use App\Core\Models\CoreModel;
use App\Core\Traits\HasTimestamps;
use App\Core\Traits\HasUuid;
use App\Core\Contracts\OwnershipBased;

class SwapTransaction extends CoreModel implements OwnershipBased
{
    use HasTimestamps, HasUuid;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'swap_id',
        'transaction_id',
        'type', // debit, credit, fee
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'type' => 'string',
        ];
    }
}
