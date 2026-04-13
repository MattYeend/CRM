<?php

namespace App\Services\Invoices;

use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;

/**
 * Handles updates to Invoice records.
 *
 * Validates incoming request data, assigns audit fields, and persists
 * updates to the invoice.
 *
 * When an invoice has line items, subtotal is derived server-side by
 * InvoiceItemObserver and cannot be overridden. When no line items exist,
 * subtotal may be set manually and total is derived as subtotal + tax.
 * Total is always derived server-side and is never accepted from the request.
 */
class InvoiceUpdaterService
{
    /**
     * Update an existing invoice.
     *
     * Extracts validated data from the request, assigns the authenticated
     * user to the audit field, and persists the changes. Total is always
     * derived server-side. When the invoice has line items, subtotal is
     * also stripped from the request and recalculated from those items.
     * When no items exist, subtotal may be set manually and total is
     * computed as subtotal + tax.
     *
     * @param  UpdateInvoiceRequest $request The request containing
     * validated invoice data.
     * @param  Invoice $invoice The invoice to update.
     *
     * @return Invoice The updated invoice instance.
     */
    public function update(
        UpdateInvoiceRequest $request,
        Invoice $invoice
    ): Invoice {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;

        // Total is always derived — never trust the frontend value
        unset($data['total']);

        $invoice->loadCount('items');

        if ($invoice->items_count > 0) {
            // Subtotal is owned by the observer when items exist
            unset($data['subtotal']);

            $invoice->update($data);

            if ($request->has('tax')) {
                $invoice->recalculateTotals();
            }
        } else {
            // No items — subtotal is manual, derive total from subtotal + tax
            $invoice->update($data);

            $fresh = $invoice->fresh();
            $invoice->update([
                'total' => $fresh->subtotal + $fresh->tax,
            ]);
        }

        return $invoice->fresh();
    }
}
