<?php

namespace App\Modules\Payment\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'payable_type' => $this->payable_type,
            'payable_id' => $this->payable_id,
            'payable' => $this->whenLoaded('payable'),
        ];
    }
}
