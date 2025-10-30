<?php

namespace App\Modules\Swap\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SwapStoreResource extends JsonResource
{
    public function toArray($request): array
    {
        // return parent::toArray($request);
        return [
            'to_amount' => $this->to_amount,
            'fee_amount' => $this->fee_amount,
            'total_amount' => $this->total_amount,
            'rate' => $this->rate,
        ];
    }
}
