<?php

namespace App\Services\ProductStockMovement;

use App\Exceptions\InsufficientStock;
use App\Http\Requests\StoreProductStockMovementRequest;
use App\Models\Product;
use App\Models\ProductStockMovement;
use Illuminate\Support\Facades\DB;

/**
 * Handles creation of ProductStockMovement records.
 *
 * Validates stock constraints, calculates quantity changes,
 * and persists both the movement and updated product quantity
 * within a database transaction.
 */
class ProductStockMovementCreatorService
{
    /**
     * Create a new product stock movement.
     *
     * Calculates the resulting stock level and ensures it does not fall
     * below zero. Persists the movement and updates the product quantity.
     *
     * @param  Product $product The product whose stock is being modified.
     * @param  StoreProductStockMovementRequest $request Validated request
     * containing movement data.
     *
     * @return ProductStockMovement The newly created stock movement record.
     *
     * @throws InsufficientStock If the resulting stock would be negative.
     */
    public function create(
        Product $product,
        StoreProductStockMovementRequest $request
    ): ProductStockMovement {
        $data = $request->validated();
        $createdBy = $request->user()->id;

        $quantityBefore = $product->quantity;

        $quantity = $data['quantity'];

        if ($data['type'] === 'out') {
            $quantity = -$quantity;
        }

        $quantityAfter = $quantityBefore + $quantity;

        if ($quantityAfter < 0) {
            throw new InsufficientStock($quantityBefore, $data['quantity']);
        }

        return $this->persistMovement(
            $product,
            $data,
            $createdBy,
            $quantityBefore,
            $quantityAfter
        );
    }

    /**
     * Persist the stock movement and update the product quantity.
     *
     * Executes within a database transaction to ensure consistency
     * between the movement record and the products stock level.
     *
     * @param  Product $product
     * @param  array $data
     * @param  int $createdBy
     * @param  int $quantityBefore
     * @param  int $quantityAfter
     *
     * @return ProductStockMovement
     */
    private function persistMovement(
        Product $product,
        array $data,
        int $createdBy,
        int $quantityBefore,
        int $quantityAfter,
    ): ProductStockMovement {
        return DB::transaction(function () use (
            $product,
            $data,
            $createdBy,
            $quantityBefore,
            $quantityAfter,
        ) {
            $movement = ProductStockMovement::create([
                ...$data,
                'product_id' => $product->id,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'created_by' => $createdBy,
            ]);

            $product->update(['quantity' => $quantityAfter]);

            return $movement;
        });
    }
}
