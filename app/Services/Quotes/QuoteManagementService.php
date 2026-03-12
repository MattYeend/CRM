<?php

namespace App\Services\Quotes;

use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;
use App\Models\Quote;

class QuoteManagementService
{
    private QuoteCreatorService $creator;
    private QuoteUpdaterService $updater;
    private QuoteDestructorService $destructor;

    public function __construct(
        QuoteCreatorService $creator,
        QuoteUpdaterService $updater,
        QuoteDestructorService $destructor,
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new quote.
     *
     * @param StoreQuoteRequest $request
     *
     * @return Quote
     */
    public function store(StoreQuoteRequest $request): Quote
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing quote.
     *
     * @param UpdateQuoteRequest $request
     *
     * @param Quote $quote
     *
     * @return Quote
     */
    public function update(
        UpdateQuoteRequest $request,
        Quote $quote
    ): Quote {
        return $this->updater->update($request, $quote);
    }

    /**
     * Delete a quote (soft delete).
     *
     * @param Quote $quote
     *
     * @return void
     */
    public function destroy(Quote $quote): void
    {
        $this->destructor->destroy($quote);
    }

    /**
     * Restore a soft-deleted quote
     *
     * @param int $id
     *
     * @return Quote
     */
    public function restore(int $id): Quote
    {
        return $this->destructor->restore($id);
    }
}
