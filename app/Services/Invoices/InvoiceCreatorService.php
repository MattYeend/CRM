<?php

namespace App\Services\Invoices;

use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Invoice;

class InvoiceCreatorService
{
    /**
     * Create a new invoice from request data.
     *
     * @param StoreInvoiceRequest $request
     *
     * @return Invoice
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
