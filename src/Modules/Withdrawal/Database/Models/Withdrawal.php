<?php

namespace App\Modules\Withdrawal\Database\Models;

use App\Core\Traits\HasTimestamps;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasTimestamps, HasUuid;

    protected $fillable = [
        'uuid',
        'user_id',
        'payment_id',
        'amount',
        'currency_id',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
