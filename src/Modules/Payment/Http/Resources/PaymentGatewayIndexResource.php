<?php

namespace App\Modules\Payment\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentGatewayIndexResource extends JsonResource
{
    public function toArray($request): array
    {

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'mode' => $this->mode,
            'type' => $this->type,
            'is_traditional' => $this->is_traditional,
            'instructions' => is_array($this->instructions) ? $this->instructions : json_decode($this->instructions),
            'supported_currencies' => is_array($this->supported_currencies) ? $this->supported_currencies : json_decode($this->supported_currencies),
            // 'credentials' => json_decode($this->credentials, true),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
