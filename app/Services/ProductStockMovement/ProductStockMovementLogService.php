<?php

namespace App\Services\ProductStockMovement;

use App\Models\Log;
use App\Models\ProductStockMovement;
use App\Models\User;

/**
 * Handles logging of ProductStockMovement actions.
 *
 * Responsible for building structured log data and delegating
 * persistence to the Log model.
 */
class ProductStockMovementLogService
{
    /**
     * Log the creation of a product stock movement.
     *
     * @param  User $user The user performing the action.
     * @param  int $userId The ID of the user performing the action.
     * @param  ProductStockMovement $productStockMovement The movement being logged.
     *
     * @return array The structured log data.
     */
    public function productStockMovementCreated(
        User $user,
        int $userId,
        ProductStockMovement $productStockMovement
    ): array {
        $data = $this->baseProductStockMovementData($productStockMovement) + [
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
     * Build base data for product stock movement logs.
     *
     * @param  ProductStockMovement $productStockMovement
     *
     * @return array
     */
    protected function baseProductStockMovementData(
        ProductStockMovement $productStockMovement
    ): array {
        return [
            'id' => $productStockMovement->id,
            'product_id' => $productStockMovement->product_id,
            'type' => $productStockMovement->type,
            'quantity' => $productStockMovement->quantity,
            'quantity_before' => $productStockMovement->quantity_before,
            'quantity_after' => $productStockMovement->quantity_after,
            'reference' => $productStockMovement->reference,
            'notes' => $productStockMovement->notes,
            'meta' => $productStockMovement->meta,
        ];
    }
}
