<?php

namespace App\Services\PartStockMovements;

use App\Models\Log;
use App\Models\PartStockMovement;
use App\Models\User;

/**
 * Handles logging of PartStockMovement actions.
 *
 * Responsible for building structured log data and delegating
 * persistence to the Log model.
 */
class PartStockMovementLogService
{
    /**
     * Log the creation of a part stock movement.
     *
     * @param  User $user The user performing the action.
     * @param  int $userId The ID of the user performing the action.
     * @param  PartStockMovement $partStockMovement The movement being logged.
     *
     * @return array The structured log data.
     */
    public function partStockMovementCreated(
        User $user,
        int $userId,
        PartStockMovement $partStockMovement
    ): array {
        $data = $this->basePartStockMovementData($partStockMovement) + [
            'created_at' => now(),
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PART_STOCK_MOVEMENT_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Build base data for part stock movement logs.
     *
     * @param  PartStockMovement $partStockMovement
     *
     * @return array
     */
    protected function basePartStockMovementData(
        PartStockMovement $partStockMovement
    ): array {
        return [
            'id' => $partStockMovement->id,
            'part_id' => $partStockMovement->part_id,
            'type' => $partStockMovement->type,
            'quantity' => $partStockMovement->quantity,
            'quantity_before' => $partStockMovement->quantity_before,
            'quantity_after' => $partStockMovement->quantity_after,
            'reference' => $partStockMovement->reference,
            'notes' => $partStockMovement->notes,
            'meta' => $partStockMovement->meta,
        ];
    }
}
