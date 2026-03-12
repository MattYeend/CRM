<?php

namespace App\Services\Orders;

use App\Models\Log;
use App\Models\Order;
use App\Models\User;

class OrderLogService
{
    public function __construct()
    {
        // Empty constructor
    }

    /**
     * Log the creation of a Order.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Order $order The order was created.
     *
     * @return Log The created log entry.
     */
    public function orderCreated(
        User $user,
        int $userId,
        Order $order
    ): array {
        $data = $this->baseOrderData($order) + [
            'created_at' => now(),
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_ORDER_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the update of a Order.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Order $order The order was updated.
     *
     * @return Log The created log entry.
     */
    public function orderUpdated(
        User $user,
        int $userId,
        Order $order
    ): array {
        $data = $this->baseOrderData($order) + [
            'updated_at' => now(),
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_ORDER_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the deletion of a Order.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Order $order The order was deleted.
     *
     * @return Log The created log entry.
     */
    public function orderDeleted(
        User $user,
        int $userId,
        Order $order
    ): array {
        $data = $this->baseOrderData($order) + [
            'deleted_at' => now(),
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_ORDER_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the restoration of a Order.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Order $order The order was restored.
     *
     * @return Log The created log entry.
     */
    public function orderRestored(
        User $user,
        int $userId,
        Order $order
    ): array {
        $data = $this->baseOrderData($order) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_ORDER_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Prepare the base data for logging a Order.
     *
     * @param Order $order The order being logged.
     *
     * @return array The base data array.
     */
    protected function baseOrderData(Order $order): array
    {
        $userId = auth()->id();

        return [
            'id' => $order->id,
            'user_id' => $userId,
            'amount' => $order->amount,
            'currency' => $order->currency,
            'status' => $order->status,
            'payment_method' => $order->payment_method,
            'paid_at' => $order->paid_at,
            'payment_intent_id' => $order->payment_intent_id,
            'charge_id' => $order->charge_id,
            'meta' => $order->meta,
        ];
    }
}
