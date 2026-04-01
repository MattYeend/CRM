<?php

namespace App\Services\Quotes;

use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;
use App\Models\Quote;

/**
 * Orchestrates quote lifecycle operations by delegating to focused
 * sub-services.
 *
 * Acts as the single entry point for quote create, update, delete, and
 * restore operations, keeping controllers decoupled from the underlying
 * service implementations.
 */
class QuoteManagementService
{
    /**
     * Service responsible for creating new quote records.
     *
     * @var QuoteCreatorService
     */
    private QuoteCreatorService $creator;

    /**
     * Service responsible for updating existing quote records.
     *
     * @var QuoteUpdaterService
     */
    private QuoteUpdaterService $updater;

    /**
     * Service responsible for soft-deleting and restoring quote records.
     *
     * @var QuoteDestructorService
     */
    private QuoteDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  QuoteCreatorService $creator Handles quote creation.
     * @param  QuoteUpdaterService $updater Handles quote updates.
     * @param  QuoteDestructorService $destructor Handles quote deletion
     * and restoration.
     */
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
     * @param  StoreQuoteRequest $request Validated request containing quote
     * data.
     *
     * @return Quote The newly created quote.
     */
    public function store(StoreQuoteRequest $request): Quote
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing quote.
     *
     * @param  UpdateQuoteRequest $request Validated request containing
     * updated quote data.
     * @param  Quote $quote The quote instance to update.
     *
     * @return Quote The updated quote.
     */
    public function update(
        UpdateQuoteRequest $request,
        Quote $quote
    ): Quote {
        return $this->updater->update($request, $quote);
    }

    /**
     * Soft-delete a quote.
     *
     * Delegates to the destructor service to perform a soft-delete.
     *
     * @param  Quote $quote The quote to delete.
     *
     * @return void
     */
    public function destroy(Quote $quote): void
    {
        $this->destructor->destroy($quote);
    }

    /**
     * Restore a soft-deleted quote.
     *
     * @param  int $id The primary key of the soft-deletedquote.
     *
     * @return Quote The restored quote.
     */
    public function restore(int $id): Quote
    {
        return $this->destructor->restore($id);
    }
}
