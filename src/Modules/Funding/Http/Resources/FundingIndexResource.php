<?php

namespace App\Modules\Funding\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FundingIndexResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'fundable_id' => $this->fundable_id,
            'fundable_type' => $this->fundable_type,
            'type' => $this->type,
            'amount' => $this->amount,
            'fiat_amount' => $this->fiat_amount,
            'fiat_currency_id' => $this->fiat_currency_id,
            'user_id' => $this->user_id,
            'currency_id' => $this->currency_id,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'fundable' => $this->whenLoaded('fundable'),
            'currency' => $this->whenLoaded('currency'),
            'fiat_currency' => $this->whenLoaded('fiatCurrency'),
        ];
    }
}
