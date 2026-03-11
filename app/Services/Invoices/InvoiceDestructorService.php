<?php

namespace App\Services\Invoices;

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
        $userId = auth()->id();

        $invoice->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
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
