<?php

namespace App\Modules\Payment\Http\Resources;

use App\Modules\Currency\Http\Resources\CurrencyResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentIndexResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'method' => $this->method,
            'payable_type' => $this->payable_type,
            'payable_id' => $this->payable_id,
            'payable' => $this->payable,
            'status' => $this->status,
            'amount' => $this->amount,
            'currency' => new CurrencyResource($this->currency),
        ];
    }
}
