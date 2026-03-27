<?php

namespace App\Services\PartStockMovements;

use App\Models\Log;
use App\Models\PartStockMovement;
use App\Models\User;

class PartStockMovementLogService
{
    /**
     * Log the creation of a Part Stock Movement.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param PartStockMovement $partStockMovement The partStockMovement
     * was created.
     *
     * @return Log The created log entry.
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
     * Prepare the base data for logging a Part Stock Movement.
     *
     * @param PartStockMovement $partStockMovement The part$partStockMovement
     * being logged.
     *
     * @return array The base data array.
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
