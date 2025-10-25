<?php

namespace App\Modules\Withdrawal\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\User\Http\Resources\UserResource;

class WithdrawalResource extends JsonResource
{
    public function toArray($request): array
    {
        dd($this->all());
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'user_id' => $this->user_id,
            'payment_id' => $this->payment_id,
            'payment' => $this->whenLoaded('payment'),
            'amount' => $this->amount,
            'currency_id' => $this->currency_id,
            // 'currency' => $this->whenLoaded('currency'),
            'user' => new UserResource($this->user),
            // 'user' => $this->whenLoaded('user'),
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at_formatted,
            'updated_at' => $this->updated_at_formatted,
            'time_ago' => $this->time_ago,
        ];
    }
}
