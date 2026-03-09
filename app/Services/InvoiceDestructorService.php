<?php

namespace App\Services;

use App\Models\Invoice;

class InvoiceDestructorService
{
    /**
     * Soft-delete a invoice.
     *
     * @param Invoice $invoice
     *
     * @return void
     */
    public function destroy(Invoice $invoice): void
    {
        $invoice->update([
            'deleted_by' => auth()->id(),
        ]);

        $invoice->delete();
    }

    /**
     * Restore a trashed invoice.
     *
     * @param int $id
     *
     * @return Invoice
     */
    public function restore(int $id): Invoice
    {
        $invoice = Invoice::withTrashed()->findOrFail($id);

        if ($invoice->trashed()) {
            $invoice->restore();
        }

        return $invoice;
    }
}
