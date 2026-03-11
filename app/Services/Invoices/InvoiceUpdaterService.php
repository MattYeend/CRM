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
        $data = $request->validated();

        $data['updated_by'] = $request->user()->id;

        $invoice->update($data);

        return $invoice->fresh();
    }
}
