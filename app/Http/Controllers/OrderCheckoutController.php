<?php

namespace App\Http\Controllers;

use App\Models\Order;

class OrderCheckoutController extends Controller
{
    public function checkout(Order $order)
    {
        $user = $order->user;

        return $user->checkoutCharge(
            $order->amount,
            'Order #' . $order->id,
            1,
            [
                'success_url' => route('orders.success', $order),
                'cancel_url' => route('orders.show', $order),
            ]
        );
    }
}
