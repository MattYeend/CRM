<?php

namespace App\Services\Orders;

use App\Models\Order;

class OrderDestructorService
{
    /**
     * Soft-delete a order.
     *
     * @param Order $order
     *
     * @return void
     */
    public function destroy(Order $order): void
    {
        $userId = auth()->id();

        $order->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);

        $order->delete();
    }

    /**
     * Restore a trashed order.
     *
     * @param int $id
     *
     * @return Order
     */
    public function restore(int $id): Order
    {
        $userId = auth()->id();

        $order = Order::withTrashed()->findOrFail($id);

        if ($order->trashed()) {
            $order->update([
                'updated_by' => $userId,
                'updated_at' => now(),
            ]);
            $order->restore();
        }

        return $order;
    }
}
