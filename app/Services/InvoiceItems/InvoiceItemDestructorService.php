<?php

namespace App\Services\InvoiceItems;

use App\Models\InvoiceItem;

class InvoiceItemDestructorService
{
    /**
     * Soft-delete a invoice item.
     *
     * @param InvoiceItem $invoiceItem
     *
     * @return void
     */
    public function destroy(InvoiceItem $invoiceItem): void
    {
        $invoiceItem->update([
            'deleted_by' => auth()->id(),
        ]);

        $invoiceItem->delete();
    }

    /**
     * Restore a trashed invoice item.
     *
     * @param int $id
     *
     * @return InvoiceItem
     */
    public function restore(int $id): InvoiceItem
    {
        $invoiceItem = InvoiceItem::withTrashed()->findOrFail($id);

        if ($invoiceItem->trashed()) {
            $invoiceItem->restore();
        }

        return $invoiceItem;
    }
}
