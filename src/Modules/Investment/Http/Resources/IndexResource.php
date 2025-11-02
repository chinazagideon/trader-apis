<?php

namespace App\Modules\Investment\Http\Resources;

use App\Modules\User\Http\Resources\UserResource;
use App\Modules\Pricing\Http\Resources\IndexResource as PricingIndexResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Investment\Enums\InvestmentStatus;
use App\Modules\Investment\Enums\InvestmentTypes;
class IndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'user_id' => $this->user_id,
            'pricing_id' => $this->pricing_id,
            'amount' => $this->amount,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'notes' => $this->notes,
            'type' => $this->formatType($this->type) ?? null,
            'risk' => $this->risk ?? null,
            'name' => $this->name ?? null,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'user' => new UserResource($this->user),
            'pricing' => new PricingIndexResource($this->pricing),
        ];
    }

    /**
     * Format the type for display
     * @param string|null $type
     * @return string
     */
    protected function formatType(?string $type): ?string
    {
        return InvestmentTypes::tryFrom($type)?->label() ?? null;
    }
}
