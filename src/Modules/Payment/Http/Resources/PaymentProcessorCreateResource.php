<?php

namespace App\Modules\Payment\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentProcessorCreateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'payment_gateway_id' => $this->payment_gateway_id,
            'payable_id' => $this->payable_id,
            'amount' => $this->amount,
            'fee' => $this->fee,
            'total_amount' => $this->total_amount,
            'market_rate' => $this->market_rate,
            'fiat_amount' => $this->fiat_amount,
            'fiat_currency' => $this->fiat_currency,
            'currency' => $this->currency,
            'status' => $this->status,
            'processor_data' => $this->processor_data,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

