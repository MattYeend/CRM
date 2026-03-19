<?php

namespace App\Services\Orders;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;

class OrderCreatorService
{
    /**
     * Create a new order from request data.
     *
     * @param StoreOrderRequest $request
     *
     * @return Order
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
