<?php

namespace App\Services\InvoiceItems;

use App\Http\Requests\UpdateInvoiceItemRequest;
use App\Models\InvoiceItem;

class InvoiceItemUpdaterService
{
    /**
     * Update the invoice item using request data.
     *
     * @param UpdateInvoiceItemRequest $request
     *
     * @param InvoiceItem $invoiceItem
     *
     * @return InvoiceItem
     */
    public function update(
        UpdateInvoiceItemRequest $request,
        InvoiceItem $invoiceItem
    ): InvoiceItem {
        $data = $request->validated();

        $data['updated_by'] = $request->user()->id;

        if (isset($data['quantity']) && isset($data['unit_price'])) {
            $data['line_total'] = $data['quantity'] * $data['unit_price'];
        }

        $invoiceItem->update($data);

        return $invoiceItem->fresh();
    }
}
