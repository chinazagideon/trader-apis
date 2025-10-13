<?php

namespace App\Modules\Transaction\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Module-specific resource for Transaction index operations
 * This will be resolved with highest priority by the new resolver
 */
class TransactionIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'narration' => $this->narration,
            'entry_type' => $this->entry_type,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'created_at' => $this->created_at,

            // Index-specific fields (lighter payload)
            // 'formatted_amount' => number_format($this->total_amount, 2),
            // 'status_badge' => $this->getStatusBadge(),
            // 'entry_type_icon' => $this->getEntryTypeIcon(),
            // 'category' => $this->category,
            // 'transactable' => $this->transactable,
        ];
    }

    /**
     * Get status badge for UI display
     */
    private function getStatusBadge(): string
    {
        return match($this->status) {
            'completed' => 'success',
            'pending' => 'warning',
            'failed' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get entry type icon for UI display
     */
    private function getEntryTypeIcon(): string
    {
        return match($this->entry_type) {
            'credit' => 'arrow-up',
            'debit' => 'arrow-down',
            default => 'minus',
        };
    }
}
