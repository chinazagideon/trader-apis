<?php

namespace App\Modules\Market\Database\Models;

use App\Core\Traits\HasTimestamps;
use App\Core\Traits\HasUuid;
use App\Core\Models\CoreModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Currency\Database\Models\Currency;
use App\Modules\Market\Database\Models\MarketPrice;

class Market extends CoreModel
{
    use HasTimestamps, HasUuid;


    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'name',
        'description',
        'image',
        'url',
        'slug',
        'status',
        'type',
        'category',
        'subcategory',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the prices for the market.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(MarketPrice::class, 'market_id');
    }

    /**
     * Get the currency that owns the market.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
