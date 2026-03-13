<?php

namespace App\Services\DealProducts;

use Illuminate\Database\Eloquent\Model;

class DealProductCreatorService
{
    /**
     * Attach product(s) to a parent (Deal, Order, Quote)
     *
     * @param Model $parent
     *
     * @param array $items Each item ['product_id', 'quantity', 'price', 'meta']
     */
    public function create(Model $parent, array $items): void
    {
        foreach ($items as $item) {
            $quantity = $item['quantity'] ?? 1;
            $price = $item['price'] ?? 0;
            $meta = $item['meta'] ?? null;

            $parent->products()->syncWithoutDetaching([
                $item['product_id'] => [
                    'quantity' => $quantity,
                    'price' => $price,
                    'meta' => $meta,
                ],
            ]);
        }
    }
}
