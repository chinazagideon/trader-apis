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
            'payment_id' => $this->payment_id,
            'payment' => $this->whenLoaded('payment'),
            'amount' => $this->amount,
            'currency_id' => $this->currency_id,
            'currency' => $this->whenLoaded('currency'),
            'user' => $this->whenLoaded('user'),
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at_formatted,
            'updated_at' => $this->updated_at_formatted,

        ];
    }
}
