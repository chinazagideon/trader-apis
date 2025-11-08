<?php

namespace App\Modules\Market\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MarketIndexResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'type' => $this->type,
            'currency_id' => $this->currency_id,
            'category' => $this->category,
            'subcategory' => $this->subcategory,
            'currency' => $this->whenLoaded('currency'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
