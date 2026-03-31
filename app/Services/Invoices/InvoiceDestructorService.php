<?php

namespace App\Services\Invoices;

use App\Models\Invoice;

/**
 * Handles soft deletion and restoration of Invoice records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by,
 * and restored_at columns are always populated.
 */
class InvoiceDestructorService
{
    /**
     * Soft-delete a invoice.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the invoice.
     *
     * @param  Invoice $invoice The invoice to soft-delete.
     *
     * @return void
     */
    public function destroy(Invoice $invoice): void
    {
        $userId = auth()->id();

        $invoice->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);

        $invoice->delete();
    }

    /**
     * Restore a soft-deleted invoice.
     *
     * Looks up the invoice item including trashed records, records the
     * authenticated user and timestamp in the audit columns, then restores
     * the invoice. Returns the invoice item unchanged if it is not
     * currently trashed.
     *
     * @param  int $id The primary key of the soft-deleted invoice item.
     *
     * @return Invoice The restored invoice instance.
     */
    public function restore(int $id): Invoice
    {
        $userId = auth()->id();

        $invoice = Invoice::withTrashed()->findOrFail($id);

        if ($invoice->trashed()) {
            $invoice->update([
                'restored_by' => $userId,
                'restored_at' => now(),
            ]);
            $invoice->restore();
        }

        return $invoice;
    }
}
