<?php

namespace App\Modules\Client\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientCreateResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'api_key' => $this->api_key,
            'name' => $this->name,
            'slug' => $this->slug,
            'config' => $this->getConfig(),
            'features' => $this->getFeatures(),
            'is_active' => $this->is_active,
            'uuid' => $this->uuid,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
