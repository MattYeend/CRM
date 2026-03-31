<?php

namespace App\Services\BillOfMaterials;

use App\Models\BillOfMaterial;
use App\Models\Log;
use App\Models\User;

/**
 * Handles audit logging for BOM lifecycle events.
 *
 * Each public method writes a structured log entry via the Log model for
 * a specific bom action, combining base bom data with
 * action-specific timestamp and actor fields.
 */
class BillOfMaterialLogService
{
    /**
     * Log the creation of a BOM entry.
     *
     * Records the user who created the BOM and the timestamp.
     *
     * @param  User $user The user performing the action.
     * @param  int $userId The ID of the user performing the action.
     * @param  BillOfMaterial $billOfMaterial The BOM entry being created.
     *
     * @return array The logged data for the creation action.
     */
    public function billOfMaterialCreated(
        User $user,
        int $userId,
        BillOfMaterial $billOfMaterial
    ): array {
        $data = $this->baseBillOfMaterialData($billOfMaterial) + [
            'created_at' => now(),
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_BILL_OF_MATERIAL_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the update of a BOM entry.
     *
     * Records the user who updated the BOM and the timestamp.
     *
     * @param  User $user The user performing the action.
     * @param  int $userId The ID of the user performing the action.
     * @param  BillOfMaterial $billOfMaterial The BOM entry being updated.
     *
     * @return array The logged data for the update action.
     */
    public function billOfMaterialUpdated(
        User $user,
        int $userId,
        BillOfMaterial $billOfMaterial
    ): array {
        $data = $this->baseBillOfMaterialData($billOfMaterial) + [
            'updated_at' => now(),
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_BILL_OF_MATERIAL_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the deletion of a BOM entry.
     *
     * Records the user who deleted the BOM and the timestamp.
     *
     * @param  User $user The user performing the action.
     * @param  int $userId The ID of the user performing the action.
     * @param  BillOfMaterial $billOfMaterial The BOM entry being deleted.
     *
     * @return array The logged data for the deletion action.
     */
    public function billOfMaterialDeleted(
        User $user,
        int $userId,
        BillOfMaterial $billOfMaterial
    ): array {
        $data = $this->baseBillOfMaterialData($billOfMaterial) + [
            'deleted_at' => now(),
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_BILL_OF_MATERIAL_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the restoration of a BOM entry.
     *
     * Records the user who restored the BOM and the timestamp.
     *
     * @param  User $user The user performing the action.
     * @param  int $userId The ID of the user performing the action.
     * @param  BillOfMaterial $billOfMaterial The BOM entry being restored.
     *
     * @return array The logged data for the restoration action.
     */
    public function billOfMaterialRestored(
        User $user,
        int $userId,
        BillOfMaterial $billOfMaterial
    ): array {
        $data = $this->baseBillOfMaterialData($billOfMaterial) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_BILL_OF_MATERIAL_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Prepare the base data for logging a BOM entry.
     *
     * Extracts relevant attributes from the BOM for logging purposes.
     *
     * @param  BillOfMaterial $billOfMaterial The BOM entry to extract
     * data from.
     *
     * @return array The base data array to be included in logs.
     */
    protected function baseBillOfMaterialData(
        BillOfMaterial $billOfMaterial
    ): array {
        return [
            'id' => $billOfMaterial->id,
            'parent_part_id' => $billOfMaterial->parent_part_id,
            'child_part_id' => $billOfMaterial->child_part_id,
            'quantity' => $billOfMaterial->quantity,
            'unit_of_measure' => $billOfMaterial->unit_of_measure,
            'notes' => $billOfMaterial->notes,
            'meta' => $billOfMaterial->meta,
        ];
    }
}
