<?php

namespace App\Services\Deals;

use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Handles read queries for Deal records.
 *
 * Delegates searching, sorting, and trash filtering to dedicated
 * sub-services and returns paginated or single deal results with
 * the appropriate relationships loaded.
 */
class DealQueryService
{
    /**
     * Service responsible for applying search filters.
     *
     * @var DealSearchService
     */
    private DealSearchService $search;

    /**
     * Service responsible for applying sort order.
     *
     * @var DealSortingService
     */
    private DealSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters.
     *
     * @var DealTrashFilterService
     */
    private DealTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  DealSearchService $search Handles search filtering.
     * @param  DealSortingService $sorting Handles sort order.
     * @param  DealTrashFilterService $trashFilter Handles trash filtering.
     */
    public function __construct(
        DealSearchService $search,
        DealSortingService $sorting,
        DealTrashFilterService $trashFilter,
    ) {
        $this->search = $search;
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of deals with search, sorting,
     * and trash filters applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry search,
     * sort, filter, and pagination params.
     *
     * @return LengthAwarePaginator Paginated deal results.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = Deal::with(
            'company',
            'owner',
            'pipeline',
            'stage',
        );

        $this->search->applySearch($query, $request);
        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single deal with related data loaded.
     *
     * @param  Deal $deal The route-model-bound deal instance.
     *
     * @return Deal The deal with relationships loaded.
     */
    public function show(Deal $deal): Deal
    {
        return $deal->load(
            'company',
            'owner',
            'pipeline',
            'stage',
            'notes',
            'tasks',
            'attachments',
        );
    }
}
