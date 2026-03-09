<?php

namespace App\Services;

use App\Http\Requests\StoreInvoiceItemRequest;
use App\Models\InvoiceItem;

class InvoiceItemCreatorService
{
    /**
     * Create a new invoice from request data.
     *
     * @param StoreInvoiceItemRequest $request
     *
     * @return InvoiceItem
     */
    public function create(StoreInvoiceItemRequest $request): InvoiceItem
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;

        if (isset($data['quantity']) && isset($data['unit_price'])) {
            $data['line_total'] = $data['quantity'] * $data['unit_price'];
        }

        return InvoiceItem::create($data);
    }
}
