<?php

namespace App\Services\PartStockMovements;

use App\Exceptions\InsufficientStock;
use App\Http\Requests\StorePartStockMovementRequest;
use App\Models\Part;
use App\Models\PartStockMovement;
use Illuminate\Support\Facades\DB;

/**
 * Handles creation of PartStockMovement records.
 *
 * Validates stock constraints, calculates quantity changes,
 * and persists both the movement and updated part quantity
 * within a database transaction.
 */
class PartStockMovementCreatorService
{
    /**
     * Create a new part stock movement.
     *
     * Calculates the resulting stock level and ensures it does not fall
     * below zero. Persists the movement and updates the part quantity.
     *
     * @param  Part $part The part whose stock is being modified.
     * @param  StorePartStockMovementRequest $request Validated request
     * containing movement data.
     *
     * @return PartStockMovement The newly created stock movement record.
     *
     * @throws InsufficientStock If the resulting stock would be negative.
     */
    public function create(
        Part $part,
        StorePartStockMovementRequest $request
    ): PartStockMovement {
        $data = $request->validated();
        $createdBy = $request->user()->id;

        $quantityBefore = $part->quantity;

        $quantity = $data['quantity'];

        if ($data['type'] === 'out') {
            $quantity = -$quantity;
        }

        $quantityAfter = $quantityBefore + $quantity;

        if ($quantityAfter < 0) {
            throw new InsufficientStock($quantityBefore, $quantity);
        }
        return $this->persistMovement(
            $part,
            $data,
            $createdBy,
            $quantityBefore,
            $quantityAfter
        );
    }

    /**
     * Persist the stock movement and update the part quantity.
     *
     * Executes within a database transaction to ensure consistency
     * between the movement record and the part's stock level.
     *
     * @param  Part $part
     * @param  array $data
     * @param  int $createdBy
     * @param  int $quantityBefore
     * @param  int $quantityAfter
     *
     * @return PartStockMovement
     */
    private function persistMovement(
        Part $part,
        array $data,
        int $createdBy,
        int $quantityBefore,
        int $quantityAfter,
    ): PartStockMovement {
        return DB::transaction(function () use (
            $part,
            $data,
            $createdBy,
            $quantityBefore,
            $quantityAfter,
        ) {
            $movement = PartStockMovement::create([
                ...$data,
                'part_id' => $part->id,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'created_by' => $createdBy,
            ]);

            $part->update(['quantity' => $quantityAfter]);

            return $movement;
        });
    }
}
