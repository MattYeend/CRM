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
        $userId = auth()->id();

        $invoiceItem->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
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
