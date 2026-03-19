<?php

namespace App\Services\Invoices;

use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;

class InvoiceUpdaterService
{
    /**
     * Update the invoice using request data.
     *
     * @param UpdateInvoiceRequest $request
     *
     * @param Invoice $invoice
     *
     * @return Invoice
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
