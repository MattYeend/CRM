<?php

namespace App\Listeners;

use App\Models\Order;

/**
 * Listens for successful Cashier payment webhook events and marks the
 * corresponding order as paid.
 *
 * Extracts the Stripe payment intent ID from the webhook payload and
 * updates any matching order's status to 'paid'. If no payment intent
 * ID is present in the payload the listener exits silently.
 */
class CashierPaymentSucceededListener
{
    /**
     * Create the event listener.
     *
     * No dependencies are required at this time.
     */
    public function __construct()
    {
        // No dependencies required yet
    }

    /**
     * Handle the incoming payment succeeded event.
     *
     * Resolves the Stripe payment intent ID from the webhook payload and
     * updates the matching order's status to 'paid'. Returns early if the
     * payment intent ID cannot be resolved.
     *
     * @param  mixed $event The incoming webhook event carrying the Stripe
     * payload.
     *
     * @return void
     */
    public function handle($event): void
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
