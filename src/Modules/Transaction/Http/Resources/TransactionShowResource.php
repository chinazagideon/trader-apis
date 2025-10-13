<?php

namespace App\Modules\Transaction\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Module-specific resource for Transaction show operations
 * This will be resolved with highest priority by the new resolver
 */
class TransactionShowResource extends JsonResource
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

            // Additional transaction-specific fields
            'formatted_amount' => '$' . number_format($this->total_amount, 2),
            'status_label' => ucfirst($this->status),
            'entry_type_label' => ucfirst($this->entry_type),
        ];
    }
}
