<?php

namespace App\Services\Quotes;

use App\Http\Requests\UpdateQuoteRequest;
use App\Models\Quote;

class QuoteUpdaterService
{
    /**
     * Update the quote using request data.
     *
     * @param UpdateTaskRequest $request
     *
     * @param Quote $quote
     *
     * @return Quote
     */
    public function update(
        UpdateQuoteRequest $request,
        Quote $quote
    ): Quote {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;
        $data['updated_at'] = now();

        $subtotal = $data['subtotal'] ?? 0;
        $tax = $data['tax'] ?? 0;

        $data['total'] = $subtotal + $tax;

        $quote->update($data);

        return $quote->fresh();
    }
}
