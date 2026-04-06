<?php

namespace App\Services\Leads;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

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

        $paginator = $query->paginate($perPage)->appends($request->query());

        $paginator->through(
            fn (Lead $lead) => $this->formatLead($lead)
        );

        $paginator->appends([
            'permissions' => [
                'create' => Gate::allows('create', Lead::class),
                'viewAny' => Gate::allows('viewAny', Lead::class),
            ],
        ]);

        return $paginator;
    }

    /**
     * Return a single lead with related data loaded.
     *
     * @param  Lead $lead The route-model-bound lead
     * instance.
     *
     * @return array
     */
    public function show(Lead $lead): array
    {
        $lead->load(
            'owner',
            'assignedTo',
        );

        return $this->formatLead($lead);
    }

    /**
     * Format a lead into a structured array.
     *
     * Includes core attributes, related data, derived accessors,
     * and authorisation permissions for the current user.
     *
     * @param  Lead $lead
     *
     * @return array
     */
    private function formatLead(Lead $lead): array
    {
        return [
            'id' => $lead->id,
            'title' => $lead->title,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'full_name' => $lead->full_name,
            'display_name' => $lead->display_name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'contact_info' => $lead->contact_info,
            'source' => $lead->source,
            'age_in_days' => $lead->age_in_days,
            'is_stale' => $lead->is_stale,
            'is_hot' => $lead->is_hot,
            'is_high_priority' => $lead->is_high_priority,
            'is_low_priority' => $lead->is_low_priority,
            'is_eligible_for_conversion' => $lead->is_eligible_for_conversion,
            'assigned_at' => $lead->assigned_at,
            'owner' => $lead->owner,
            'assigned_to' => $lead->assignedTo,
            'creator' => $lead->creator,
            'permissions' => [
                'view' => Gate::allows('view', $lead),
                'update' => Gate::allows('update', $lead),
                'delete' => Gate::allows('delete', $lead),
            ],
        ];
    }

}
