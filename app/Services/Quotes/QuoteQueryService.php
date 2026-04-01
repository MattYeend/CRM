<?php

namespace App\Services\Quotes;

use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Handles read queries for Quote records.
 *
 * Delegates searching, sorting, and trash filtering to dedicated
 * sub-services and returns paginated or single quote results with
 * the appropriate relationships loaded.
 */
class QuoteQueryService
{
    /**
     * Service responsible for applying sort order.
     *
     * @var QuoteSortingService
     */
    private QuoteSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters.
     *
     * @var QuoteTrashFilterService
     */
    private QuoteTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  QuoteSortingService $sorting Handles sort order.
     * @param  QuoteTrashFilterService $trashFilter Handles
     * trash filtering.
     */
    public function __construct(
        QuoteSortingService $sorting,
        QuoteTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of quotes with search, sorting,
     * and trash filters applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry search,
     * sort, filter, and pagination params.
     *
     * @return LengthAwarePaginator Paginated quotes item results.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = Quote::with('creator', 'updater', 'deal');

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single quote with related data loaded.
     *
     * @param  Quote $quote The route-model-bound oquoterder
     * instance.
     *
     * @return Quote The quote with relationships loaded.
     */
    public function show(Quote $quote): Quote
    {
        return $quote->load('creator', 'updater', 'deal');
    }
}
