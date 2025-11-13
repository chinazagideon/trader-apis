<?php

namespace App\Modules\Investment\Http\Resources;

use App\Modules\User\Http\Resources\UserResource;
use App\Modules\Pricing\Http\Resources\IndexResource as PricingIndexResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'pricing_id' => $this->pricing_id,
            'amount' => $this->amount,
            'status' => $this->status,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'notes' => $this->notes,
            'user' => $this->whenLoaded('user'),
            'pricing' => $this->whenLoaded('pricing'),
            // 'transactionable' => $this->transactionable,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

