<?php

namespace App\Services\PartStockMovements;

use App\Exceptions\InsufficientStock;
use App\Http\Requests\StorePartStockMovementRequest;
use App\Models\Part;
use App\Models\PartStockMovement;
use Illuminate\Support\Facades\DB;

class PartStockMovementCreatorService
{
    /**
     * Create a new part stock movement from request data.
     *
     * @param Part $part
     *
     * @param StorePartStockMovementRequest $request
     *
     * @return PartStockMovement
     */
    public function create(
        Part $part,
        StorePartStockMovementRequest $request
    ): PartStockMovement {
        $data = $request->validated();
        $createdBy = $request->user()->id;
        $quantityBefore = $part->quantity;
        $quantityAfter = $quantityBefore + $data['quantity'];

        if ($quantityAfter < 0) {
            throw new InsufficientStock($quantityBefore, $data['quantity']);
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
     * Persist the movement and update part quantity in a transaction.
     *
     * @param Part $part
     *
     * @param array $data
     *
     * @param int $createdBy
     *
     * @param int $quantityBefore
     *
     * @param int $quantityAfter
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
