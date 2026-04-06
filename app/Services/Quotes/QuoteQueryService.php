<?php

namespace App\Services\Quotes;

use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

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

        $query = Quote::with(
            'creator',
            'updater',
            'deal',
        );

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $query->paginate($perPage)->appends($request->query());

        $paginator->through(
            fn (Quote $quote) => $this->formatQuote($quote)
        );

        $paginator->appends([
            'permissions' => [
                'create' => Gate::allows('create', Quote::class),
                'viewAny' => Gate::allows('viewAny', Quote::class),
            ],
        ]);

        return $paginator;
    }

    /**
     * Return a single quote with related data loaded.
     *
     * @param  Quote $quote The route-model-bound oquoterder
     * instance.
     *
     * @return array
     */
    public function show(Quote $quote): array
    {
        $quote->load(
            'deal',
            'products',
            'creator',
        );

        return $this->formatQuote($quote);
    }

    /**
     * Format a quote into a structured array.
     *
     * Includes core attributes, related deal and product data, derived
     * state flags, formatted financials, and authorisation permissions
     * for the current user.
     *
     * @param  Quote $quote
     *
     * @return array
     */
    private function formatQuote(Quote $quote): array
    {
        return [
            'id' => $quote->id,
            'deal_id' => $quote->deal_id,
            'deal' => $quote->deal,
            'products' => $quote->products,
            'currency' => $quote->currency,
            'subtotal' => $quote->subtotal,
            'formatted_subtotal' => $quote->getFormattedSubtotalAttribute(),
            'tax' => $quote->tax,
            'formatted_tax' => $quote->getFormattedTaxAttribute(),
            'total' => $quote->total,
            'formatted_total' => $quote->getFormattedTotalAttribute(),
            'sent_at' => $quote->sent_at,
            'accepted_at' => $quote->accepted_at,
            'is_sent' => $quote->getIsSentAttribute(),
            'is_accepted' => $quote->getIsAcceptedAttribute(),
            'creator' => $quote->creator,
            'permissions' => [
                'view' => Gate::allows('view', $quote),
                'update' => Gate::allows('update', $quote),
                'delete' => Gate::allows('delete', $quote),
            ],
        ];
    }
}
