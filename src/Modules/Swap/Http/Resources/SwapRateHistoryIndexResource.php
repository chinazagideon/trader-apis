<?php


namespace App\Modules\Swap\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SwapRateHistoryIndexResource extends JsonResource
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
            'from_currency' => $this->whenLoaded('fromCurrency'),

            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
