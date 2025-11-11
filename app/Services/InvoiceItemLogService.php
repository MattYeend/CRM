<?php

namespace App\Services;

use App\Models\InvoiceItem;
use App\Models\Log;
use App\Models\User;

class InvoiceItemLogService
{
    public function __construct()
    {
        // Empty constructor
    }

    /**
     * Log the creation of an Invoice Item.
     *
     * @param User $user The user that created the invoice item.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param InvoiceItem $invoiceItem The invoice item being logged.
     *
     * @return Log The created log entry.
     */
    public function invoiceItemCreated(
        User $user,
        int $userId,
        InvoiceItem $invoiceItem
    ): array {
        $data = $this->baseInvoiceItemData($invoiceItem, $user) + [
            'created_at' => $invoiceItem->created_at,
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_INVOICE_ITEM_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the update of an Invoice Item.
     *
     * @param User $user The user that updated the invoice item.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param InvoiceItem $invoiceItem The invoice item being logged.
     *
     * @return Log The created log entry.
     */
    public function invoiceItemUpdated(
        User $user,
        int $userId,
        InvoiceItem $invoiceItem
    ): array {
        $data = $this->baseInvoiceItemData($invoiceItem, $user) + [
            'updated_at' => $invoiceItem->updated_at,
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_INVOICE_ITEM_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the deletion of an Invoice Item.
     *
     * @param User $user The user that deleted the invoice item.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param InvoiceItem $invoiceItem The invoice item being logged.
     *
     * @return Log The created log entry.
     */
    public function invoiceItemDeleted(
        User $user,
        int $userId,
        InvoiceItem $invoiceItem
    ): array {
        $data = $this->baseInvoiceItemData($invoiceItem, $user) + [
            'deleted_at' => now(),
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_INVOICE_ITEM_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the restoration of an Invoice Item.
     *
     * @param User $user The user that restored the invoice item.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param InvoiceItem $invoiceItem The invoice item being logged.
     *
     * @return Log The created log entry.
     */
    public function invoiceItemRestored(
        User $user,
        int $userId,
        InvoiceItem $invoiceItem
    ): array {
        $data = $this->baseInvoiceItemData($invoiceItem, $user) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_INVOICE_ITEM_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Prepare base data for an Invoice Item log entry.
     *
     * @param InvoiceItem $invoiceItem The invoice item being logged.
     *
     * @param User $user The user that performed the action.
     *
     * @return array The base data for the log entry.
     */
    protected function baseInvoiceItemData(
        InvoiceItem $invoiceItem
    ): array {
        return [
            'id' => $invoiceItem->id,
            'invoice_id' => $invoiceItem->invoice_id,
            'product_id' => $invoiceItem->product_id,
            'description' => $invoiceItem->description,
            'quantity' => $invoiceItem->quantity,
            'unit_price' => $invoiceItem->unit_price,
            'line_total' => $invoiceItem->line_total,
            'meta' => $invoiceItem->meta,
        ];
    }
}
