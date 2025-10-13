<?php

namespace App\Modules\Transaction\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Sub-resource specific resource for TransactionCategory show operations
 * This will be resolved with highest priority by the new sub-resource resolver
 * Pattern: TransactionCategoryShowResource
 */
class TransactionCategoryShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
            'icon' => $this->icon,
            'is_active' => $this->is_active,
            'parent_id' => $this->parent_id,
            'sort_order' => $this->sort_order,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // TransactionCategory-specific fields
            'formatted_name' => $this->getFormattedName(),
            'status_badge' => $this->getStatusBadge(),
            'color_preview' => $this->getColorPreview(),
            'parent_category' => $this->whenLoaded('parent'),
            'sub_categories' => $this->whenLoaded('children'),
            'transaction_count' => $this->whenCounted('transactions'),
        ];
    }

    /**
     * Get formatted name with status indicator
     */
    private function getFormattedName(): string
    {
        $status = $this->is_active ? '✓' : '✗';
        return "{$status} {$this->name}";
    }

    /**
     * Get status badge for UI display
     */
    private function getStatusBadge(): string
    {
        return $this->is_active ? 'success' : 'secondary';
    }

    /**
     * Get color preview for UI display
     */
    private function getColorPreview(): array
    {
        return [
            'hex' => $this->color,
            'rgb' => $this->hexToRgb($this->color),
            'contrast' => $this->getContrastColor($this->color),
        ];
    }

    /**
     * Convert hex color to RGB
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Get contrast color (black or white) for text readability
     */
    private function getContrastColor(string $hex): string
    {
        $rgb = $this->hexToRgb($hex);
        $luminance = (0.299 * $rgb['r'] + 0.587 * $rgb['g'] + 0.114 * $rgb['b']) / 255;
        return $luminance > 0.5 ? '#000000' : '#FFFFFF';
    }
}
