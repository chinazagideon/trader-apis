<?php

namespace App\Modules\Withdrawal\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Withdrawal\Enums\WithrawalTypes;
class WithdrawalStoreResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'user_id' => $this->user_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'notes' => $this->notes,
            'type' => $this->getTypeLabel($this->type),
            'created_at' => $this->created_at_formatted,
            'updated_at' => $this->updated_at_formatted,
            'time_ago' => $this->time_ago,
            'withdrawable' => $this->whenLoaded('withdrawable'),
            'currency' => $this->whenLoaded('currency'),
            'user' => $this->whenLoaded('user'),
        ];
    }

    /**
     * Get the label for the withdrawal type
     * @param string|null $type
     * @return string
     */
    private function getTypeLabel(?string $type): string
    {
        return $type ? WithrawalTypes::from($type)->label() : 'Unknown';
    }
}
