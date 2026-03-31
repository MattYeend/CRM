<?php

namespace App\Services\Leads;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Handles read queries for Lead records.
 *
 * Delegates searching, sorting, and trash filtering to dedicated
 * sub-services and returns paginated or single lead results with
 * the appropriate relationships loaded.
 */
class LeadQueryService
{
    /**
     * Service responsible for applying the search.
     *
     * @var LeadSearchService
     */
    private LeadSearchService $search;

    /**
     * Service responsible for applying sort order.
     *
     * @var LeadSortingService
     */
    private LeadSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters.
     *
     * @var LeadTrashFilterService
     */
    private LeadTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  LeadSearchService $search Handles search filtering.
     * @param  LeadSortingService $sorting Handles sort order.
     * @param  LeadTrashFilterService $trashFilter Handles
     * trash filtering.
     */
    public function __construct(
        LeadSearchService $search,
        LeadSortingService $sorting,
        LeadTrashFilterService $trashFilter,
    ) {
        $this->search = $search;
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of leads with search, sorting,
     * and trash filters applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry search,
     * sort, filter, and pagination params.
     *
     * @return LengthAwarePaginator Paginated leads item results.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = Lead::with(
            'owner',
            'assignedTo',
        );

        $this->search->applySearch($query, $request);
        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single lead with related data loaded.
     *
     * @param  Lead $lead The route-model-bound lead
     * instance.
     *
     * @return Lead The lead with relationships loaded.
     */
    public function show(Lead $lead): Lead
    {
        return $lead->load(
            'owner',
            'assignedTo',
        );
    }
}
