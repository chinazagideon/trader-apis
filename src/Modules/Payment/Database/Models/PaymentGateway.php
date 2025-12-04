<?php

namespace App\Modules\Payment\Database\Models;

use App\Core\Models\CoreModel;
use App\Core\Traits\HasTimestamps;
use App\Core\Traits\HasUuid;
use App\Core\Traits\HasClientApp;
use App\Core\Traits\HasClientScope;

class PaymentGateway extends CoreModel
{
    use HasTimestamps;
    use HasUuid;
    use HasClientApp;
    use HasClientScope;


    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'mode', // live, test
        'type', // crypto, fiat
        'is_traditional', // true, false
        'instructions',
        'supported_currencies',
        'credentials',
        'is_active',
        'client_id',
    ];

    protected function casts(): array
    {
        return [
            'credentials' => 'array',
            'supported_currencies' => 'array',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'instructions' => 'array',
        ];
    }
}
