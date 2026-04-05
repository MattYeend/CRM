<?php

namespace App\Services\Quotes;

use App\Http\Requests\UpdateQuoteRequest;
use App\Models\Quote;

/**
 * Handles updates to Quote records.
 *
 * Validates incoming request data, assigns audit fields, and persists
 * updates to the quote.
 */
class QuoteUpdaterService
{
    /**
     * Update an existing quote.
     *
     * Extracts validated data from the request, assigns the authenticated
     * user and timestamp to audit fields, updates the quote, and returns
     * a fresh instance.
     *
     * @param  UpdateQuoteRequest $request The request containing
     * validated quote data.
     * @param  Quote $quote The quote to update.
     *
     * @return Quote The updated quote instance.
     */
    public function update(
        UpdateQuoteRequest $request,
        Quote $quote
    ): Quote {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;

        $subtotal = $data['subtotal'] ?? 0;
        $tax = $data['tax'] ?? 0;

        $data['total'] = $subtotal + $tax;

        $quote->update($data);

        return $quote->fresh();
    }
}
