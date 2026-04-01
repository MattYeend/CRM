<?php

namespace App\Services\Orders;

use App\Models\Order;

/**
 * Handles soft deletion and restoration of Order records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by, and
 * restored_at columns are always populated.
 */
class OrderDestructorService
{
    /**
     * Soft-delete a order.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the order.
     *
     * @param  Order $order The order instance to soft-delete.
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
     * Restore a soft-deleted order.
     *
     * Looks up the order including trashed records, records the
     * authenticated user and timestamp in the audit columns, then restores
     * the order. Returns the order unchanged if it is not currently
     * trashed.
     *
     * @param  int $id The primary key of the soft-deleted order.
     *
     * @return Order The restored order instance.
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
