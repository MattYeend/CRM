<?php

namespace App\Services\QuoteProducts;

use Illuminate\Database\Eloquent\Model;

/**
 * Handles updates to product relationships on parent models.
 *
 * Updates pivot data such as quantity, price, and metadata for
 * products already attached to the parent model.
 */
class QuoteProductUpdaterService
{
    /**
     * Update pivot data for products attached to a parent model.
     *
     * Iterates over the provided items and updates the pivot data for
     * each associated product. Defaults are applied for missing values.
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
            $quantity = $item['quantity'] ?? 1;
            $price = $item['price'] ?? 0;
            $meta = $item['meta'] ?? null;

            $parent->products()->updateExistingPivot($item['product_id'], [
                'quantity' => $quantity,
                'price' => $price,
                'meta' => $meta,
            ]);
        }
    }
}
