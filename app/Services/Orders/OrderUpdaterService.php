<?php

namespace App\Services\Orders;

use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;

class OrderUpdaterService
{
    /**
     * Update the order using request data.
     *
     * @param UpdateOrderRequest $request
     *
     * @param Order $order
     *
     * @return Order
     */
    public function update(
        UpdateOrderRequest $request,
        Order $order
    ): Order {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;
        $data['updated_at'] = now();

        $order->update($data);

        return $order->fresh();
    }
}
