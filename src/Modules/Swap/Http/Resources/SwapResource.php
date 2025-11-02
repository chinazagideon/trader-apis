<?php

namespace App\Modules\Swap\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SwapResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'user_id' => $this->user_id,
            'from_currency_id' => $this->from_currency_id,
            'to_currency_id' => $this->to_currency_id,
            'from_currency' => $this->whenLoaded('fromCurrency'),
            'to_currency' => $this->whenLoaded('toCurrency'),
            'user' => $this->whenLoaded('user'),
            'from_amount' => $this->from_amount,
            'to_amount' => $this->to_amount,
            'fee_amount' => $this->fee_amount,
            'total_amount' => $this->total_amount,
            'rate' => $this->rate,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
