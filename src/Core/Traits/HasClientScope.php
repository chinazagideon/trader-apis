<?php

namespace App\Core\Traits;


use App\Core\Traits\HasClientApp;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Client\Database\Models\Client;
use Illuminate\Contracts\Database\Eloquent\Builder;

/**
 * Trait to add client scope to the model
 *
 * @package App\Core\Traits
 */
trait HasClientScope
{
    use HasClientApp;

    /**
     * Get the client that owns the model
     *
     * @return BelongsTo
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * boot client scope
     *
     * @return void
     */
    protected static function bootHasClientScope()
    {
        static::addGlobalScope(new \App\Core\Models\ClientScope);

        static::creating(function ($model) {
            if (app()->bound('current_client_id') && !$model->client_id) {
                $model->client_id = app('current_client_id');
            }
        });
    }

    /**
     * get specific client scope
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeForClient(Builder $query): Builder
    {
        return $query->where($this->getTable().'.client_id', $this->getClientId());
    }
}
