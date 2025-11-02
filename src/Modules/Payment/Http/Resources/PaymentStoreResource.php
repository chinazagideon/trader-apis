<?php

namespace App\Modules\Payment\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentStoreResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'payable_type' => $this->payable_type,
            'payable_id' => $this->payable_id,
            'status' => $this->status,
            'amount' => $this->amount,
            'currency' => $this->currency,
        ];
    }
}
