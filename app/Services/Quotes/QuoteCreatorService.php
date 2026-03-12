<?php

namespace App\Services\Quotes;

use App\Http\Requests\StoreQuoteRequest;
use App\Models\Quote;

class QuoteCreatorService
{
    /**
     * Create a new quote from request data.
     *
     * @param StoreQuoteRequest $request
     *
     * @return Quote
     */
    public function create(StoreQuoteRequest $request): Quote
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;
        $data['created_at'] = now();

        $subtotal = $data['subtotal'] ?? 0;
        $tax = $data['tax'] ?? 0;

        $data['total'] = $subtotal + $tax;

        return Quote::create($data);
    }
}
