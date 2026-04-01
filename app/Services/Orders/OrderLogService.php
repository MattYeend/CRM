<?php

namespace App\Services\Orders;

use App\Models\Log;
use App\Models\Order;
use App\Models\User;

/**
 * Handles audit logging for Order lifecycle events.
 *
 * Each public method writes a structured log entry via the Log model for
 * a specific order action, combining base order data with
 * action-specific timestamp and actor fields.
 */
class OrderLogService
{
    /**
     * Log a order creation event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Order $order The order that was created.
     *
     * @return array The structured data written to the log entry.
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
     * Log a order update event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Order $order The order that was updated.
     *
     * @return array The structured data written to the log entry.
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
     * Log a order deletion event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Order $order The order that was deleted.
     *
     * @return array The structured data written to the log entry.
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
     * Log a order restoration event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Order $order The order that was restored.
     *
     * @return array The structured data written to the log entry.
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
     * Build the base data array shared across all order log entries.
     *
     * @param  Order $order The order being logged.
     *
     * @return array The base fields extracted from the order.
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
