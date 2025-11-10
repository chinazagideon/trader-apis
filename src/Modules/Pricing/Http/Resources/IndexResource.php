<?php

namespace App\Modules\Pricing\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'client_id' => $this->client_id,
            'min_amount' => $this->min_amount,
            'max_amount' => $this->max_amount,
            'lifespan' => $this->lifespan,
            'contract' => $this->contract,
            'type' => $this->type,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'currency' => $this->whenLoaded('currency'),
            'benefits' => $this->benefits,
            'roi' => $this->roi,
        ];
    }
}
