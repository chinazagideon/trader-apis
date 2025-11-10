<?php

namespace App\Modules\Pricing\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PricingCreateResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'type' => $this->type,
            'contract' => $this->contract,
            'benefits' => $this->benefits,
            'roi' => $this->roi,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'currency' => $this->whenLoaded('currency'),
        ];
    }
}
