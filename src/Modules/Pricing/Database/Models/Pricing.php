<?php

namespace App\Modules\Pricing\Database\Models;

use App\Core\Traits\HasTimestamps;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Pricing extends Model
{
    use HasTimestamps, HasUuid;

    protected $table = 'pricings';

    protected $fillable = [
        'name',
        'min_amount',
        'max_amount',
        'currency_id',
        'lifespan',
        'contract',
        'type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
            'is_active' => 'boolean',
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
