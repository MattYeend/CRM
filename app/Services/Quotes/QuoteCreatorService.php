<?php

namespace App\Services\Quotes;

use App\Http\Requests\StoreQuoteRequest;
use App\Models\Quote;

/**
 * Handles the creation of new Quote records.
 *
 * Extracts validated data from the request, stamps the creator and
 * creation timestamp, and persists the new Quote.
 */
class QuoteCreatorService
{
    /**
     * Create a new quote from the validated request data.
     *
     * Sets the created_by and created_at audit fields from the authenticated
     * user before persisting the record.
     *
     * @param  StoreQuoteRequest $request Validated request containing quote
     * data.
     *
     * @return Quote The newly created quote record.
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
