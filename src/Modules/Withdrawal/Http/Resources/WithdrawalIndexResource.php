<?php

namespace App\Modules\Withdrawal\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\User\Http\Resources\UserResource;
class WithdrawalIndexResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'user_id' => $this->user_id,
            'withdrawable_id' => $this->withdrawable_id,
            'withdrawable_type' => $this->withdrawable_type,
            'amount' => $this->amount,
            'fiat_amount' => $this->fiat_amount,
            'type' => $this->type,
            'currency_id' => $this->currency_id,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at_formatted,
            'updated_at' => $this->updated_at_formatted,
            'withdrawable' => $this->whenLoaded('withdrawable'),
            'currency' => $this->whenLoaded('currency'),
            'fiat_currency' => $this->whenLoaded('fiatCurrency'),
            'user' => $this->whenLoaded('user'),
            'method' => $this->method ?? [],
        ];
    }


}
