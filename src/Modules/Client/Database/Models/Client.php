<?php

namespace App\Modules\Client\Database\Models;

use App\Core\Models\CoreModel;
use App\Core\Traits\HasTimestamps;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Client extends CoreModel
{
    use HasTimestamps, HasUuid;

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'api_key',
        'api_secret',
        'config',
        'features',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'features' => 'json',
        'is_active' => 'boolean',
    ];

    /**
     * Get the client model
     *
     * @return Model
     */
    public function clientModal(): Model
    {
        return $this;
    }

    /**
     * Get the config
     *
     * @return
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get the features
     *
     * @return array|null
     */
    public function getFeatures()
    {
        return $this->features ?? [];
    }

}
