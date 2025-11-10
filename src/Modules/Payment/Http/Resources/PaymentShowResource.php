<?php
namespace App\Modules\Payment\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentShowResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'payable_type' => $this->payable_type,
            'payable_id' => $this->payable_id,
            'payable' => $this->whenLoaded('payable'),
            'status' => $this->status,
            'amount' => $this->amount,
            'currency_id' => $this->currency_id,
            'currency' => $this->whenLoaded('currency'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
