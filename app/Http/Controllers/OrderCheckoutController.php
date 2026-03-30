<?php

namespace App\Http\Controllers;

use App\Models\Order;

/**
 * Handles HTTP requests for the Order checkout process.
 *
 * Delegates payment initiation to the order's associated user via the
 * checkoutCharge method, passing order details and redirect URLs for
 * success and cancellation flows.
 *
 * All responses are returned as redirect responses to the payment
 * provider, making this controller suitable for consumption by the
 * Vue frontend or any API client.
 */
class OrderCheckoutController extends Controller
{
    /**
     * Initiate a checkout session for the specified order.
     *
     * Resolves the order's owner and delegates to the user's checkoutCharge
     * method, passing the order amount, a descriptive label, and redirect
     * URLs for both the success and cancellation flows.
     *
     * @param  Order $order Route-model-bound order instance to check out.
     *
     * @return mixed A redirect response to the payment provider's hosted
     * checkout page.
     */
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
