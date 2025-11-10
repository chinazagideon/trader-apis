<?php

namespace App\Modules\Withdrawal\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
class WithdrawalStoreResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'user_id' => $this->user_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'fiat_amount' => $this->fiat_amount,
            'status' => $this->status,
            'notes' => $this->notes,
            'type' => $this->type,
            'created_at' => $this->created_at_formatted,
            'updated_at' => $this->updated_at_formatted,
            'time_ago' => $this->time_ago,
            'withdrawable' => $this->whenLoaded('withdrawable'),
            'currency' => $this->whenLoaded('currency'),
            'user' => $this->whenLoaded('user'),
            'fiat_currency' => $this->whenLoaded('fiatCurrency'),
        ];
    }


}
