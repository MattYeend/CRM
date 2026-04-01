<?php

namespace App\Services\Orders;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;

/**
 * Handles the creation of new Order records.
 *
 * Extracts validated data from the request, stamps the creator and
 * creation timestamp, and persists the new Order.
 */
class OrderCreatorService
{
    /**
     * Create a new order from the validated request data.
     *
     * Sets the created_by and created_at audit fields from the authenticated
     * user before persisting the record.
     *
     * @param  StoreOrderRequest $request Validated request containing order
     * data.
     *
     * @return Order The newly created order record.
     */
    public function create(StoreOrderRequest $request): Order
    {
        $user = $request->user();
        $data = $request->validated();

        if (! app()->runningUnitTests()) {
            $user->createOrGetStripeCustomer();
        }

        $data['created_by'] = $user->id;
        $data['created_at'] = now();

        return Order::create($data);
    }
}
