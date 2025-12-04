<?php

namespace App\Modules\Pricing\Database\Models;

use App\Core\Models\CoreModel;
use App\Core\Traits\HasClientScope;
use App\Core\Traits\HasClientApp;
use App\Core\Traits\HasTimestamps;
use App\Core\Traits\HasUuid;


class Pricing extends CoreModel
{
    use HasTimestamps;
    use HasUuid;
    use HasClientScope;
    use HasClientApp;

    protected $table = 'pricings';

    protected $fillable = [
        'client_id',
        'name',
        'min_amount',
        'max_amount',
        'currency_id',
        'lifespan',
        'contract',
        'type',
        'is_active',
        'benefits',
        'roi',
    ];

    protected function casts(): array
    {
        return [
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
            'is_active' => 'boolean',
            'benefits' => 'array',
            'roi' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the currency that owns the pricing.
     */
    public function currency()
    {
        return $this->belongsTo(\App\Modules\Currency\Database\Models\Currency::class);
    }
}
