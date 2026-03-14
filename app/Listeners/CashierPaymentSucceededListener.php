<?php

namespace App\Listeners;

use App\Models\Order;

class CashierPaymentSucceededListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        // No dependencies required yet
    }

    /**
     * Handle the event.
     */
    public function handle($event)
    {
        $payload = $event->payload;

        $paymentIntent = $payload['data']['object']['id'] ?? null;

        if (! $paymentIntent) {
            return;
        }

        Order::where('stripe_payment_intent', $paymentIntent)
            ->update(['status' => 'paid']);
    }
}
