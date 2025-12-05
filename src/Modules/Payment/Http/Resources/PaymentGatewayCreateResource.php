<?php

namespace App\Modules\Payment\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentGatewayCreateResource extends JsonResource
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
            'instructions' => $this->instructions ?? [],  // Always return as array
            'supported_currencies' => $this->supported_currencies ?? [],  // Always return as array
            // 'credentials' => $this->credentials ?? [],  // Always return as array
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

