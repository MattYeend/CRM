<?php

namespace App\Services\BillOfMaterials;

use App\Models\BillOfMaterial;
use App\Models\Log;
use App\Models\User;

class BillOfMaterialLogService
{
    /**
     * Log the creation of a Bill Of Material.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param BillOfMaterial $billOfMaterial The bill of material
     * was created.
     *
     * @return Log The created log entry.
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
     * Log the update of a Bill Of Material.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param BillOfMaterial $billOfMaterial The bom was updated.
     *
     * @return Log The created log entry.
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
     * Log the deletion of a Bill Of Material.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param BillOfMaterial $billOfMaterial The bom was deleted.
     *
     * @return Log The created log entry.
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
     * Log the restoration of a Bill Of Material
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param BillOfMaterial $billOfMaterial The bom was restored.
     *
     * @return Log The created log entry.
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
     * Prepare the base data for logging a Bill Of Material.
     *
     * @param BillOfMaterial $billOfMaterial The billOfMaterial
     * being logged.
     *
     * @return array The base data array.
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
