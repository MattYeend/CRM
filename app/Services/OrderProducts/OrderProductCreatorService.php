<?php

namespace App\Services\OrderProducts;

use Illuminate\Database\Eloquent\Model;

/**
 * Handles creation of product relationships on parent models.
 *
 * Attaches one or more products to a parent model (e.g. Deal, Order, Quote)
 * via a pivot table, including quantity, price, and optional metadata.
 */
class OrderProductCreatorService
{
    /**
     * Attach product(s) to a parent model.
     *
     * Iterates over the provided items and attaches each product to the
     * parent model without removing existing relationships. Defaults are
     * applied for missing quantity, price, and meta values.
     *
     * The total is derived from quantity * price and stored on the pivot
     * automatically — it does not need to be supplied by the caller.
     *
     * @param  Model $parent The parent model to attach products to.
     * @param  array $items Array of product data, each containing:
     *                      - product_id (int)
     *                      - quantity (int, optional)
     *                      - price (float, optional)
     *                      - meta (array|null, optional)
     *
     * @return void
     */
    public function create(Model $parent, array $items): void
    {
        foreach ($items as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'] ?? 1;
            $price = $item['price'] ?? 0;
            $total = $quantity * $price;
            $meta = $item['meta'] ?? null;

            $existing = $parent->products()
                ->where('product_id', $productId)
                ->first();

            if ($existing) {
                $newQuantity = $existing->pivot->quantity + $quantity;
                $total = $newQuantity * $price;

                $parent->products()->updateExistingPivot($productId, [
                    'quantity' => $newQuantity,
                    'price' => $price,
                    'total' => $total,
                    'meta' => $meta,
                ]);
            } else {
                $parent->products()->attach($productId, [
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $quantity * $price,
                    'meta' => $meta,
                ]);
            }
        }
    }
}
