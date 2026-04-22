<?php

namespace App\Services\DealProducts;

use Illuminate\Database\Eloquent\Model;

/**
 * Handles updates to product relationships on parent models.
 *
 * Updates pivot data such as quantity, price, and metadata for
 * products already attached to the parent model.
 */
class DealProductUpdaterService
{
    /**
     * Update pivot data for products attached to a parent model.
     *
     * Iterates over the provided items and updates the pivot data for
     * each associated product. Defaults are applied for missing values.
     *
     * The total is derived from quantity * price and stored on the pivot
     * automatically - it does not need to be supplied by the caller.
     *
     * @param  Model $parent The parent model.
     * @param  array $items Array of product data, each containing:
     *                      - product_id (int)
     *                      - quantity (int, optional)
     *                      - price (float, optional)
     *                      - meta (array|null, optional)
     *
     * @return void
     */
    public function update(Model $parent, array $items): void
    {
        foreach ($items as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'] ?? 1;
            $price = $item['price'] ?? 0;
            $total = $quantity * $price;
            $meta = $item['meta'] ?? null;

            $parent->products()->updateExistingPivot($productId, [
                'quantity' => $quantity,
                'price' => $price,
                'total' => $total,
                'meta' => $meta,
            ]);
        }
    }
}
