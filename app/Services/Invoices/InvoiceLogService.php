<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
use App\Models\Log;
use App\Models\User;

/**
 * Handles logging of actions performed on Invoice models.
 *
 * Supports creation, updates, deletion, and restoration logs.
 * Builds base log data and appends user and timestamp information.
 */
class InvoiceLogService
{
    /**
     * Log the creation of an invoice.
     *
     * @param  User $user  The user performing the action
     * @param  int $userId  The ID of the user performing the action
     * @param  Invoice $invoice  The invoice that was created
     *
     * @return array
     */
    public function invoiceCreated(
        User $user,
        int $userId,
        Invoice $invoice
    ): array {
        $data = $this->baseInvoiceData($invoice) + [
            'created_at' => now(),
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_INVOICE_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the update of an invoice.
     *
     * @param  User $user
     * @param  int $userId
     * @param  Invoice $invoice
     *
     * @return array
     */
    public function invoiceUpdated(
        User $user,
        int $userId,
        Invoice $invoice
    ): array {
        $data = $this->baseInvoiceData($invoice) + [
            'updated_at' => now(),
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_INVOICE_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the deletion of an invoice.
     *
     * @param  User $user
     * @param  int $userId
     * @param  Invoice $invoice
     *
     * @return array
     */
    public function invoiceDeleted(
        User $user,
        int $userId,
        Invoice $invoice
    ): array {
        $data = $this->baseInvoiceData($invoice) + [
            'deleted_at' => now(),
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_INVOICE_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the restoration of an invoice.
     *
     * @param  User  $user
     * @param  int $userId
     * @param  Invoice $invoice
     *
     * @return array
     */
    public function invoiceRestored(
        User $user,
        int $userId,
        Invoice $invoice
    ): array {
        $data = $this->baseInvoiceData($invoice) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_INVOICE_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the sending of an Invoice.
     *
     * @param User $user The user being logged.
     * @param int $userId The ID of the user who performed the action.
     * @param Invoice $invoice The invoice was sent.
     *
     * @return array
     */
    public function invoiceSent(
        User $user,
        int $userId,
        Invoice $invoice
    ): array {
        $data = $this->baseInvoiceData($invoice) + [
            'sent_at' => now(),
            'sent_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_INVOICE_SENT,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the payment of an Invoice.
     *
     * @param User $user The user being logged.
     * @param int $userId The ID of the user who performed the action.
     * @param Invoice $invoice The invoice was paid.
     *
     * @return array
     */
    public function invoicePaid(
        User $user,
        int $userId,
        Invoice $invoice
    ): array {
        $data = $this->baseInvoiceData($invoice) + [
            'paid_at' => now(),
            'paid_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_INVOICE_PAID,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the overdue status of an Invoice.
     *
     * @param User $user The user being logged.
     * @param int $userId The ID of the user who performed the action.
     * @param Invoice $invoice The invoice was marked as overdue.
     *
     * @return array
     */
    public function invoiceOverdue(
        User $user,
        int $userId,
        Invoice $invoice
    ): array {
        $data = $this->baseInvoiceData($invoice) + [
            'overdue_at' => now(),
            'marked_overdue_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_INVOICE_OVERDUE,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Build base data for invoice log entries.
     *
     * @param  Invoice $invoice
     *
     * @return array
     */
    private function baseInvoiceData(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'number' => $invoice->number,
            'company_id' => $invoice->company_id,
            'issue_date' => $invoice->issue_date,
            'due_date' => $invoice->due_date,
            'status' => $invoice->status,
            'subtotal' => $invoice->subtotal,
            'tax' => $invoice->tax,
            'total' => $invoice->total,
            'currency' => $invoice->currency,
            'meta' => $invoice->meta,
        ];
    }
}
