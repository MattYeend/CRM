<?php

namespace App\Services\Orders;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;

class OrderManagementService
{
    private OrderCreatorService $creator;
    private OrderUpdaterService $updater;
    private OrderDestructorService $destructor;

    public function __construct(
        OrderCreatorService $creator,
        OrderUpdaterService $updater,
        OrderDestructorService $destructor,
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new order.
     *
     * @param StoreOrderRequest $request
     *
     * @return Order
     */
    public function store(StoreOrderRequest $request): Order
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing order.
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
        return $this->updater->update($request, $order);
    }

    /**
     * Delete a order (soft delete).
     *
     * @param Order $order
     *
     * @return void
     */
    public function destroy(Order $order): void
    {
        $this->destructor->destroy($order);
    }

    /**
     * Restore a soft-deleted order.
     *
     * @param int $id
     *
     * @return Order
     */
    public function restore(int $id): Order
    {
        return $this->destructor->restore($id);
    }
}
