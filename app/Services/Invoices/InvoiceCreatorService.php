<?php

namespace App\Services\Invoices;

use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Invoice;

/**
 * Handles creation of Invoice records.
 *
 * Validates incoming request data, assigns audit fields, and persists
 * a new invoice record.
 */
class InvoiceCreatorService
{
    /**
     * Create a new invoice.
     *
     * Extracts validated data from the request, assigns the authenticated
     * user and timestamp to audit fields, and creates the invoice record.
     *
     * @param  StoreInvoiceRequest $request The request containing validated
     * invoice data.
     *
     * @return Invoice The newly created invoice instance.
     */
    public function create(StoreInvoiceRequest $request): Invoice
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;
        $data['created_at'] = now();

        return Invoice::create($data);
    }
}
