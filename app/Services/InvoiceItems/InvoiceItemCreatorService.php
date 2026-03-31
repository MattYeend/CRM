<?php

namespace App\Services\InvoiceItems;

use App\Http\Requests\StoreInvoiceItemRequest;
use App\Models\InvoiceItem;

/**
 * Handles creation of InvoiceItem records.
 *
 * Validates incoming request data, assigns audit fields, and persists
 * a new invoiceItem record.
 */
class InvoiceItemCreatorService
{
    /**
     * Create a new invoiceItem.
     *
     * Extracts validated data from the request, assigns the authenticated
     * user and timestamp to audit fields, and creates the invoiceItem record.
     *
     * @param  StoreInvoiceItemRequest $request The request containing validated
     * invoiceItem data.
     *
     * @return InvoiceItem The newly created invoiceItem instance.
     */
    public function create(StoreInvoiceItemRequest $request): InvoiceItem
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;
        $data['created_at'] = now();

        if (isset($data['quantity']) && isset($data['unit_price'])) {
            $data['line_total'] = $data['quantity'] * $data['unit_price'];
        }

        return InvoiceItem::create($data);
    }
}
