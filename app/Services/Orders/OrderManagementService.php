<?php

namespace App\Services\Orders;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;

/**
 * Orchestrates order lifecycle operations by delegating to focused
 * sub-services.
 *
 * Acts as the single entry point for order create, update, delete, and
 * restore operations, keeping controllers decoupled from the underlying
 * service implementations.
 */
class OrderManagementService
{
    /**
     * Service responsible for creating new order records.
     *
     * @var OrderCreatorService
     */
    private OrderCreatorService $creator;

    /**
     * Service responsible for updating existing order records.
     *
     * @var OrderUpdaterService
     */
    private OrderUpdaterService $updater;

    /**
     * Service responsible for soft-deleting and restoring order records.
     *
     * @var OrderDestructorService
     */
    private OrderDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  OrderCreatorService $creator Handles order creation.
     * @param  OrderUpdaterService $updater Handles order updates.
     * @param  OrderDestructorService $destructor Handles order deletion
     * and restoration.
     */
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
     * @param  StoreOrderRequest $request Validated request containing order
     * data.
     *
     * @return Order The newly created order.
     */
    public function store(StoreOrderRequest $request): Order
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing order.
     *
     * @param  UpdateOrderRequest $request Validated request containing
     * updated order data.
     * @param  Order $order The order instance to update.
     *
     * @return Order The updated order.
     */
    public function update(
        UpdateOrderRequest $request,
        Order $order
    ): Order {
        return $this->updater->update($request, $order);
    }

    /**
     * Soft-delete a order.
     *
     * Delegates to the destructor service to perform a soft-delete.
     *
     * @param  Order $order The order to delete.
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
     * @param  int $id The primary key of the soft-deleted order.
     *
     * @return Order The restored order.
     */
    public function restore(int $id): Order
    {
        return $this->destructor->restore($id);
    }
}
