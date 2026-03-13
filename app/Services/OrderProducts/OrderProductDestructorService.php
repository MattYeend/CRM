<?php

namespace App\Services\OrderProducts;

use Illuminate\Database\Eloquent\Model;

class OrderProductDestructorService
{
    /**
     * Remove pivot relation
     *
     * @param Model $order
     * @param int $productId
     */
    public function remove(Model $order, int $productId): void
    {
        $order->products()->detach($productId);
    }

    /**
     * Restore pivot relation (if using soft deletes on pivot)
     *
     * @param Model $order
     * @param int $productId
     */
    public function restore(Model $order, int $productId): void
    {
        $pivot = $order->products()
            ->withTrashed()
            ->wherePivot('product_id', $productId)
            ->first();
        if ($pivot && method_exists($pivot->pivot, 'restore')) {
            $pivot->pivot->restore();
        }
    }
}
