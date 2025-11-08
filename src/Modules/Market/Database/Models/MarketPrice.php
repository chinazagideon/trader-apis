<?php

namespace App\Modules\Market\Database\Models;

use App\Core\Models\CoreModel;
use App\Core\Traits\HasTimestamps;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Currency\Database\Models\Currency;
use App\Modules\Market\Database\Models\Market;

class MarketPrice extends CoreModel
{
    use HasTimestamps, HasUuid;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'market_id',
        'currency_id',
        'price',
        'market_cap',
        'total_supply',
        'max_supply',
        'circulating_supply',
        'total_volume',
        'total_volume_24h',
        'total_volume_7d',
        'total_volume_30d',
        'total_volume_90d',
        'total_volume_180d',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'market_cap' => 'decimal:2',
            'total_supply' => 'decimal:2',
            'max_supply' => 'decimal:2',
            'circulating_supply' => 'decimal:2',
            'total_volume' => 'decimal:2',
            'total_volume_24h' => 'decimal:2',
            'total_volume_7d' => 'decimal:2',
            'total_volume_30d' => 'decimal:2',
            'total_volume_90d' => 'decimal:2',
            'total_volume_180d' => 'decimal:2',
        ];
    }

    /**
     * Get the market that owns the price.
     */
    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    /**
     * Get the currency that owns the price.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
