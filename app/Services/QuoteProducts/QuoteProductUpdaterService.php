<?php

namespace App\Services\QuoteProducts;

use Illuminate\Database\Eloquent\Model;

class QuoteProductUpdaterService
{
    /**
     * Update pivot data for products attached to a quote.
     *
     * Updates the quantity, price, and meta fields for existing products
     * on the pivot table.
     *
     * @param Model $quote The quote model whose products should be updated
     * @param array $items Array of products to update with keys:
     *                     - 'product_id' (int)
     *                     - 'quantity' (int|null)
     *                     - 'price' (float|null)
     *                     - 'meta' (array|null)
     *
     * @return void
     */
    public function update(Model $quote, array $items): void
    {
        foreach ($items as $item) {
            $quote->products()->updateExistingPivot($item['product_id'], [
                'quantity' => $item['quantity'] ?? 1,
                'price'    => $item['price'] ?? 0,
                'meta'     => $item['meta'] ?? null,
            ]);
        }
    }
}
