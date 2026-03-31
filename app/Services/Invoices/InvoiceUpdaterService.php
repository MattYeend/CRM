<?php

namespace App\Services\Invoices;

use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;

/**
 * Handles updates to Invoice records.
 *
 * Validates incoming request data, assigns audit fields, and persists
 * updates to the invoice.
 */
class InvoiceUpdaterService
{
    /**
     * Update an existing invoice.
     *
     * Extracts validated data from the request, assigns the authenticated
     * user and timestamp to audit fields, updates the invoice, and returns
     * a fresh instance.
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
        $data['updated_at'] = now();

        $invoice->update($data);

        return $invoice->fresh();
    }
}
