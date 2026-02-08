<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Log;
use App\Models\User;

class InvoiceLogService
{
    public function __construct()
    {
        // Empty constructor
    }

    /**
     * Log the creation of an Invoice.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Invoice $invoice The invoice that was created.
     *
     * @return Log The created log entry.
     */
    public function invoiceCreated(
        User $user,
        int $userId,
        Invoice $invoice
    ): array {
        $data = $this->baseInvoiceData($invoice) + [
            'created_at' => $invoice->created_at,
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
     * Log the update of an Invoice.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Invoice $invoice The invoice that was updated.
     *
     * @return Log The created log entry.
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
     * Log the deletion of an Invoice.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Invoice $invoice The invoice that was deleted.
     *
     * @return Log The created log entry.
     */
    public function invoiceDeleted(
        User $user,
        int $userId,
        Invoice $invoice
    ): array {
        $data = $this->baseInvoiceData($invoice) + [
            'deleted_at' => $invoice->deleted_at,
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
     * Log the restoration of an Invoice.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Invoice $invoice The invoice that was restored.
     *
     * @return Log The created log entry.
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
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Invoice $invoice The invoice was sent.
     *
     * @return Log The created log entry.
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
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Invoice $invoice The invoice was paid.
     *
     * @return Log The created log entry.
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
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Invoice $invoice The invoice was marked as overdue.
     *
     * @return Log The created log entry.
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
     * Build the common data array for an Invoice log entry.
     *
     * @param Invoice $invoice
     *
     * @return array
     */
    private function baseInvoiceData(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'number' => $invoice->number,
            'company_id' => $invoice->company_id,
            'contact_id' => $invoice->contact_id,
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
