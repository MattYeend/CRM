<?php

namespace App\Services\InvoiceItems;

use App\Models\InvoiceItem;
use App\Models\Log;
use App\Models\User;

/**
 * Handles logging of actions performed on InvoiceItem models.
 *
 * Supports creation, updates, deletion, and restoration logs.
 * Builds base log data and appends user and timestamp information.
 */
class InvoiceItemLogService
{
    /**
     * Log the creation of an invoice item.
     *
     * @param  User $user  The user performing the action
     * @param  int $userId  The ID of the user performing the action
     * @param  InvoiceItem $invoiceItem  The invoice item that was created
     *
     * @return array
     */
    public function invoiceItemCreated(
        User $user,
        int $userId,
        InvoiceItem $invoiceItem
    ): array {
        $data = $this->baseInvoiceItemData($invoiceItem) + [
            'created_at' => now(),
            'created_by' => $user->name,
        ];

        Log::log(Log::ACTION_INVOICE_ITEM_CREATED, $data, $userId);

        return $data;
    }

    /**
     * Log the update of an invoice item.
     *
     * @param  User $user
     * @param  int $userId
     * @param  InvoiceItem $invoiceItem
     *
     * @return array
     */
    public function invoiceItemUpdated(
        User $user,
        int $userId,
        InvoiceItem $invoiceItem
    ): array {
        $data = $this->baseInvoiceItemData($invoiceItem) + [
            'updated_at' => now(),
            'updated_by' => $user->name,
        ];

        Log::log(Log::ACTION_INVOICE_ITEM_UPDATED, $data, $userId);

        return $data;
    }

    /**
     * Log the deletion of an invoice item.
     *
     * @param  User $user
     * @param  int $userId
     * @param  InvoiceItem $invoiceItem
     *
     * @return array
     */
    public function invoiceItemDeleted(
        User $user,
        int $userId,
        InvoiceItem $invoiceItem
    ): array {
        $data = $this->baseInvoiceItemData($invoiceItem) + [
            'deleted_at' => now(),
            'deleted_by' => $user->name,
        ];

        Log::log(Log::ACTION_INVOICE_ITEM_DELETED, $data, $userId);

        return $data;
    }

    /**
     * Log the restoration of an invoice item.
     *
     * @param  User  $user
     * @param  int $userId
     * @param  InvoiceItem $invoiceItem
     *
     * @return array
     */
    public function invoiceItemRestored(
        User $user,
        int $userId,
        InvoiceItem $invoiceItem
    ): array {
        $data = $this->baseInvoiceItemData($invoiceItem) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(Log::ACTION_INVOICE_ITEM_RESTORED, $data, $userId);

        return $data;
    }

    /**
     * Build base data for invoice item log entries.
     *
     * @param  InvoiceItem $invoiceItem
     *
     * @return array
     */
    protected function baseInvoiceItemData(InvoiceItem $invoiceItem): array
    {
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
