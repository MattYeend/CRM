<?php

namespace App\Services\OrderProducts;

use Illuminate\Database\Eloquent\Model;

class OrderProductCreatorService
{
    /**
     * Attach product(s) to an Order.
     *
     * @param Model $order
     * @param array $items Each item ['product_id', 'quantity', 'price', 'meta']
     */
    public function create(Model $order, array $items): void
    {
        foreach ($items as $item) {
            $quantity = $item['quantity'] ?? 1;
            $price = $item['price'] ?? 0;
            $meta = $item['meta'] ?? null;

            $order->products()->syncWithoutDetaching([
                $item['product_id'] => [
                    'quantity' => $quantity,
                    'price' => $price,
                    'meta' => $meta,
                ],
            ]);
        }
    }
}
