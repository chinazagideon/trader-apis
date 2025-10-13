<?php

namespace App\Modules\Transaction\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for TransactionCategory pivot table operations
 * This resource works with the transactions_category pivot table
 */
class TransactionCategoryIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'transaction_id' => $this->transaction_id,
            'category_id' => $this->category_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Load the related category data if available
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'description' => $this->category->description,
                    'type' => $this->category->type,
                    'color' => $this->category->color,
                    'icon' => $this->category->icon,
                    'status' => $this->category->status,
                    'entity_types' => $this->category->entity_types,
                    'operations' => $this->category->operations,
                    'metadata' => $this->category->metadata,
                ];
            }),

            // Load the related transaction data if available
            'transaction' => $this->whenLoaded('transaction', function () {
                return [
                    'id' => $this->transaction->id,
                    'uuid' => $this->transaction->uuid,
                    'total_amount' => $this->transaction->total_amount,
                    'entry_type' => $this->transaction->entry_type,
                    'status' => $this->transaction->status,
                    'narration' => $this->transaction->narration,
                ];
            }),
        ];
    }
}
