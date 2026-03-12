<?php

namespace App\Services\DealProducts;

use Illuminate\Database\Eloquent\Model;

class DealProductUpdaterService
{
    /**
     * Update pivot data for products attached to a parent
     *
     * @param Model $parent
     *
     * @param array $items Each item ['product_id', 'quantity', 'price', 'meta']
     */
    public function update(Model $parent, array $items): void
    {
        foreach ($items as $item) {
            $parent->products()->updateExistingPivot($item['product_id'], [
                'quantity' => $item['quantity'] ?? 1,
                'price' => $item['price'] ?? 0,
                'meta' => $item['meta'] ?? null,
            ]);
        }
    }
}
