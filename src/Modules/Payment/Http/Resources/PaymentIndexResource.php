<?php

namespace App\Modules\Payment\Http\Resources;

use App\Modules\Currency\Http\Resources\CurrencyResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentIndexResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'payable_type' => $this->payable_type,
            'payable_id' => $this->payable_id,
            'status' => $this->status,
            'amount' => $this->amount,
            'currency_id' => $this->currency_id,
            'currency' => $this->currency,
        ];
    }
}
