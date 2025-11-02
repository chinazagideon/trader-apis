<?php

namespace App\Modules\Swap\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SwapRateHistoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'from_currency_id' => $this->from_currency_id,
            'to_currency_id' => $this->to_currency_id,
            'rate' => $this->rate,
            'spread' => $this->spread,
            'source' => $this->source,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'from_currency' => $this->whenLoaded('fromCurrency'),
            'to_currency' => $this->whenLoaded('toCurrency'),

            // Computed fields
            'formatted_rate' => number_format($this->rate, 8),
            'rate_percentage' => $this->rate * 100,
            'source_label' => ucfirst(str_replace('_', ' ', $this->source)),
        ];
    }
}
