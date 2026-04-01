<?php

namespace App\Services\Parts;

use App\Models\Log;
use App\Models\Part;
use App\Models\User;

/**
 * Handles audit logging for Part lifecycle events.
 *
 * Each public method writes a structured log entry via the Log model for
 * a specific part action, combining base part data with action-specific
 * timestamp and actor fields. Base part data is composed from focused private
 * methods covering identity, relationships, physical attributes, pricing,
 * inventory, flags, and metadata.
 */
class PartLogService
{
    /**
     * Log a part creation event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Part $part The part that was created.
     *
     * @return array The structured data written to the log entry.
     */
    public function partCreated(
        User $user,
        int $userId,
        Part $part
    ): array {
        $data = $this->basePartData($part) + [
            'created_at' => now(),
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PART_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a part update event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Part $part The part that was updated.
     *
     * @return array The structured data written to the log entry.
     */
    public function partUpdated(
        User $user,
        int $userId,
        Part $part
    ): array {
        $data = $this->basePartData($part) + [
            'updated_at' => now(),
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PART_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a part deletion event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Part $part The part that was deleted.
     *
     * @return array The structured data written to the log entry.
     */
    public function partDeleted(
        User $user,
        int $userId,
        Part $part
    ): array {
        $data = $this->basePartData($part) + [
            'deleted_at' => now(),
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PART_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a part restoration event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Part $part The part that was restored.
     *
     * @return array The structured data written to the log entry.
     */
    public function partRestored(
        User $user,
        int $userId,
        Part $part
    ): array {
        $data = $this->basePartData($part) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PART_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Build the base data array shared across all part log entries.
     *
     * Merges all data sub-groups into a single array covering identity,
     * relationships, physical attributes, pricing, inventory, flags, and
     * metadata.
     *
     * @param  Part $part The part being logged.
     *
     * @return array The complete base data array.
     */
    protected function basePartData(Part $part): array
    {
        return array_merge(
            $this->baseData($part),
            $this->relationshipData($part),
            $this->identifyData($part),
            $this->physicalData($part),
            $this->pricingData($part),
            $this->inventoryData($part),
            $this->flagData($part),
            $this->metaData($part),
        );
    }

    /**
     * Build the primary key data for the log entry.
     *
     * @param  Part $part The part being logged.
     *
     * @return array
     */
    private function baseData(Part $part): array
    {
        return [
            'id' => $part->id,
        ];
    }

    /**
     * Build the relationship data for the log entry.
     *
     * Includes product, category, and supplier IDs, and conditionally
     * includes category name and slug when the relationship is loaded.
     *
     * @param  Part $part The part being logged.
     *
     * @return array
     */
    private function relationshipData(Part $part): array
    {
        return [
            'product_id' => $part->product_id,
            'category_id' => $part->category_id,
            'supplier_id' => $part->supplier_id,
            'category' => $part->relationLoaded('category', fn () => [
                'id' => $part->category->id,
                'name' => $part->category->name,
                'slug' => $part->category->slug,
            ]),
        ];
    }

    /**
     * Build the identity and classification data for the log entry.
     *
     * @param  Part $part The part being logged.
     *
     * @return array
     */
    private function identifyData(Part $part): array
    {
        return [
            'sku' => $part->sku,
            'part_number' => $part->part_number,
            'barcode' => $part->barcode,
            'name' => $part->name,
            'description' => $part->description,
            'brand' => $part->brand,
            'manufacturer' => $part->manufacturer,
            'type' => $part->type,
            'status' => $part->status,
            'unit_of_measure' => $part->unit_of_measure,
        ];
    }

    /**
     * Build the physical dimension and material data for the log entry.
     *
     * @param  Part $part The part being logged.
     *
     * @return array
     */
    private function physicalData(Part $part): array
    {
        return [
            'height' => $part->height,
            'width' => $part->width,
            'length' => $part->length,
            'weight' => $part->weight,
            'volume' => $part->volume,
            'colour' => $part->colour,
            'material' => $part->material,
        ];
    }

    /**
     * Build the pricing and tax data for the log entry.
     *
     * @param  Part $part The part being logged.
     *
     * @return array
     */
    private function pricingData(Part $part): array
    {
        return [
            'price' => $part->price,
            'cost_price' => $part->cost_price,
            'currency' => $part->currency,
            'tax_rate' => $part->tax_rate,
            'tax_code' => $part->tax_code,
            'discount_percentage' => $part->discount_percentage,
        ];
    }

    /**
     * Build the stock level and warehouse location data for the log entry.
     *
     * @param  Part $part The part being logged.
     *
     * @return array
     */
    private function inventoryData(Part $part): array
    {
        return [
            'quantity' => $part->quantity,
            'min_stock_level' => $part->min_stock_level,
            'max_stock_level' => $part->max_stock_level,
            'reorder_point' => $part->reorder_point,
            'reorder_quantity' => $part->reorder_quantity,
            'lead_time_days' => $part->lead_time_days,
            'warehouse_location' => $part->warehouse_location,
            'bin_location' => $part->bin_location,
        ];
    }

    /**
     * Build the boolean feature and state flag data for the log entry.
     *
     * @param  Part $part The part being logged.
     *
     * @return array
     */
    private function flagData(Part $part): array
    {
        return [
            'is_active' => $part->is_active,
            'is_purchasable' => $part->is_purchasable,
            'is_sellable' => $part->is_sellable,
            'is_manufactured' => $part->is_manufactured,
            'is_serialised' => $part->is_serialised,
            'is_batch_tracked' => $part->is_batch_tracked,
            'is_test' => $part->is_test,
        ];
    }

    /**
     * Build the metadata for the log entry.
     *
     * @param  Part $part The part being logged.
     *
     * @return array
     */
    private function metaData(Part $part): array
    {
        return [
            'meta' => $part->meta,
        ];
    }
}
