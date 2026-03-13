<?php

namespace App\Services\QuoteProducts;

use Illuminate\Database\Eloquent\Model;

class QuoteProductCreatorService
{
    /**
     * Attach product(s) to a quote.
     *
     * Uses `syncWithoutDetaching` to avoid removing existing pivot relations.
     *
     * @param Model $quote The quote model to attach products to
     * @param array $items Array of products with keys:
     *                     - 'product_id' (int) : Product ID
     *                     - 'quantity' (int|null) : Quantity (default 1)
     *                     - 'price' (float|null) : Price (default 0)
     *                     - 'meta' (array|null) : Optional metadata
     *
     * @return void
     */
    public function create(Model $quote, array $items): void
    {
        foreach ($items as $item) {
            $quantity = $item['quantity'] ?? 1;
            $price = $item['price'] ?? 0;
            $meta = $item['meta'] ?? null;

            $quote->products()->syncWithoutDetaching([
                $item['product_id'] => [
                    'quantity' => $quantity,
                    'price' => $price,
                    'meta' => $meta,
                ],
            ]);
        }
    }
}
