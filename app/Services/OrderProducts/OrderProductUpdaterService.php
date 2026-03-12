<?php

namespace App\Services\OrderProducts;

use Illuminate\Database\Eloquent\Model;

class OrderProductUpdaterService
{
    /**
     * Update pivot data for products attached to an Order.
     *
     * @param Model $order
     * @param array $items Each item ['product_id', 'quantity', 'price', 'meta']
     */
    public function update(Model $order, array $items): void
    {
        foreach ($items as $item) {
            $order->products()->updateExistingPivot($item['product_id'], [
                'quantity' => $item['quantity'] ?? 1,
                'price'    => $item['price'] ?? 0,
                'meta'     => $item['meta'] ?? null,
            ]);
        }
    }
}
