<?php

namespace App\Services\Orders;

use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;

/**
 * Handles updates to Order records.
 *
 * Validates incoming request data, assigns audit fields, and persists
 * updates to the order.
 */
class OrderUpdaterService
{
    /**
     * Update an existing order.
     *
     * Extracts validated data from the request, assigns the authenticated
     * user and timestamp to audit fields, updates the order, and returns
     * a fresh instance.
     *
     * @param  UpdateOrderRequest $request The request containing
     * validated order data.
     * @param  Order $order The order to update.
     *
     * @return Order The updated order instance.
     */
    public function update(
        UpdateOrderRequest $request,
        Order $order
    ): Order {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;

        $order->update($data);

        return $order->fresh();
    }
}
