<?php

namespace App\Modules\Market\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MarketPriceIndexResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'market_id' => $this->market_id,
            'market' => $this->whenLoaded('market'),
            'currency_id' => $this->currency_id,
            'currency' => $this->whenLoaded('currency'),
            'price' => $this->price,
            'market_cap' => $this->market_cap,
            'total_supply' => $this->total_supply,
            'max_supply' => $this->max_supply,
            'circulating_supply' => $this->circulating_supply,
            'total_volume' => $this->total_volume,
            'total_volume_24h' => $this->total_volume_24h,
            'total_volume_7d' => $this->total_volume_7d,
            'total_volume_30d' => $this->total_volume_30d,
            'total_volume_90d' => $this->total_volume_90d,
            'total_volume_180d' => $this->total_volume_180d,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
