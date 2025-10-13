<?php

namespace App\Modules\Transaction\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Generic Transaction resource (fallback strategy)
 * This will be used if no specific resource is found
 */
class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'transactable_id' => $this->transactable_id,
            'transactable_type' => $this->transactable_type,
            'narration' => $this->narration,
            'entry_type' => $this->entry_type,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
