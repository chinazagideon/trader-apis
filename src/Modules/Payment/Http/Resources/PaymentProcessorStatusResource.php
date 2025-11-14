<?php

namespace App\Modules\Payment\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentProcessorStatusResource extends JsonResource
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
            'payment' => $this->whenLoaded('payment'),
        ];
    }
}
