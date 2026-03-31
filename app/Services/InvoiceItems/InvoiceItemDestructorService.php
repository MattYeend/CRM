<?php

namespace App\Services\InvoiceItems;

use App\Models\InvoiceItem;

/**
 * Handles soft deletion and restoration of InvoiceItem records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by,
 * and restored_at columns are always populated.
 */
class InvoiceItemDestructorService
{
    /**
     * Soft-delete a invoice item.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the invoice item.
     *
     * @param  InvoiceItem $invoiceItem The invoiceItem to soft-delete.
     *
     * @return void
     */
    public function destroy(InvoiceItem $invoiceItem): void
    {
        $userId = auth()->id();

        $invoiceItem->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);

        $invoiceItem->delete();
    }

    /**
     * Restore a soft-deleted invoiceItem.
     *
     * Looks up the invoice item including trashed records, records the
     * authenticated user and timestamp in the audit columns, then restores
     * the invoiceItem. Returns the invoice item unchanged if it is not
     * currently trashed.
     *
     * @param  int $id The primary key of the soft-deleted invoice item.
     *
     * @return InvoiceItem The restored invoiceItem instance.
     */
    public function restore(int $id): InvoiceItem
    {
        $userId = auth()->id();

        $invoiceItem = InvoiceItem::withTrashed()->findOrFail($id);

        if ($invoiceItem->trashed()) {
            $invoiceItem->update([
                'restored_by' => $userId,
                'restored_at' => now(),
            ]);
            $invoiceItem->restore();
        }

        return $invoiceItem;
    }
}
