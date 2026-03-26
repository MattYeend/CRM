<?php

namespace App\Services\Parts;

use App\Models\Log;
use App\Models\Part;
use App\Models\User;

class PartLogService
{
    /**
     * Log the creation of a Part.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Part $part The part was created.
     *
     * @return Log The created log entry.
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
     * Log the update of a Part.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Part $part The part was updated.
     *
     * @return Log The created log entry.
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
     * Log the deletion of a Part.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Part $part The part was deleted.
     *
     * @return Log The created log entry.
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
     * Log the restoration of a Part.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Part $part The part was restored.
     *
     * @return Log The created log entry.
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
     * Prepare the base data for logging a Part.
     *
     * @param Part $part The part being logged.
     *
     * @return array The base data array.
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
     * Base data
     *
     * @param Part $part The part being logged.
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
     * Relationship data
     *
     * @param Part $part The part being logged.
     *
     * @return array
     */
    private function relationshipData(Part $part): array
    {
        return [
            'product_id' => $part->product_id,
            'category_id' => $part->category_id,
            'supplier_id' => $part->supplier_id,
            'category' => $part->whenLoaded('category', fn () => [
                'id' => $part->category->id,
                'name' => $part->category->name,
                'slug' => $part->category->slug,
            ]),
        ];
    }

    /**
     * Identify data
     *
     * @param Part $part The part being logged.
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
     * Phyiscal data
     *
     * @param Part $part The part being logged.
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
     * Pricing data
     *
     * @param Part $part The part being logged.
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
     * Inventory data
     *
     * @param Part $part The part being logged.
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
     * Flag data
     *
     * @param Part $part The part being logged.
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
     * Meta data
     *
     * @param Part $part The part being logged.
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
