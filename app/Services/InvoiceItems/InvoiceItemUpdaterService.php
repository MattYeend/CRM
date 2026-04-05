<?php

namespace App\Services\InvoiceItems;

use App\Http\Requests\UpdateInvoiceItemRequest;
use App\Models\InvoiceItem;

/**
 * Handles updates to InvoiceItem records.
 *
 * Validates incoming request data, assigns audit fields, and persists
 * updates to the invoice item.
 */
class InvoiceItemUpdaterService
{
    /**
     * Update an existing invoice item.
     *
     * Extracts validated data from the request, assigns the authenticated
     * user and timestamp to audit fields, updates the invoice item, and returns
     * a fresh instance.
     *
     * @param  UpdateInvoiceItemRequest $request The request containing
     * validated invoice item data.
     * @param  InvoiceItem $invoiceItem The invoice item to update.
     *
     * @return InvoiceItem The updated invoice item instance.
     */
    public function update(
        UpdateInvoiceItemRequest $request,
        InvoiceItem $invoiceItem
    ): InvoiceItem {
        $user = $request->user();
        $data = $request->validated();

        $data['updated_by'] = $user->id;

        if (isset($data['quantity'], $data['unit_price'])) {
            $data['line_total'] = $data['quantity'] * $data['unit_price'];
        }

        $invoiceItem->update($data);

        return $invoiceItem->fresh();
    }
}
