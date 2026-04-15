<?php

namespace App\Services\Leads;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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
     * @param  LeadTrashFilterService $trashFilter Handles trash filtering.
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
     * @return array Paginated lead results with top-level permissions.
     */
    public function list(Request $request): array
    {
        $query = Lead::with(
            'owner',
            'assignedTo',
            'creator',
            'updater',
            'deleter',
        );

        $this->search->applySearch($query, $request);
        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $this->paginate($query, $request);

        return array_merge(
            $paginator,
            ['permissions' => $this->getPermissions()]
        );
    }

    /**
     * Return a single lead with related data loaded.
     *
     * @param  Lead $lead The route-model-bound lead instance.
     *
     * @return array
     */
    public function show(Lead $lead): array
    {
        $lead->load(
            'owner',
            'assignedTo',
            'creator',
            'updater',
            'deleter',
        );

        return $this->formatLead($lead);
    }

    /**
     * Paginate and transform the lead query.
     *
     * @param  Builder $query
     * @param  Request $request
     *
     * @return array
     */
    private function paginate(Builder $query, Request $request): array
    {
        $perPage = max(1, min((int) $request->query('per_page', 10), 100));

        return $query->paginate($perPage)
            ->appends($request->query())
            ->through(fn (
                Lead $lead
            ): array => $this->formatLead($lead))
            ->toArray();
    }

    /**
     * Get permission flags for the current user.
     *
     * @return array
     */
    private function getPermissions(): array
    {
        return [
            'create' => Gate::allows('create', Lead::class),
            'viewAny' => Gate::allows('viewAny', Lead::class),
        ];
    }

    /**
     * Format a lead into a structured array.
     *
     * Combines core attributes, derived flags, relationships,
     * and authorisation permissions for the current user.
     *
     * @param  Lead $lead
     *
     * @return array
     */
    private function formatLead(Lead $lead): array
    {
        return array_merge(
            $this->baseData($lead),
            $this->derivedData($lead),
            $this->assignmentData($lead),
            $this->relationshipData($lead),
            $this->permissionData($lead),
        );
    }

    /**
     * Extract core lead attributes.
     *
     * @param  Lead $lead
     *
     * @return array
     */
    private function baseData(Lead $lead): array
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
            'assigned_at' => $lead->assigned_at,
            'meta' => $lead->meta,
            'created_at' => $lead->created_at,
            'updated_at' => $lead->updated_at,
        ];
    }

    /**
     * Extract computed lead flags for priority and status.
     *
     * @param  Lead $lead
     *
     * @return array
     */
    private function derivedData(Lead $lead): array
    {
        return [
            'is_stale' => $lead->is_stale,
            'is_hot' => $lead->is_hot,
            'is_high_priority' => $lead->is_high_priority,
            'is_low_priority' => $lead->is_low_priority,
            'is_eligible_for_conversion' => $lead->is_eligible_for_conversion,
        ];
    }

    /**
     * Extract lead assignment data.
     *
     * @param  Lead $lead
     *
     * @return array
     */
    private function assignmentData(Lead $lead): array
    {
        return [
            'assigned_to' => $lead->assignedTo,
            'owner' => $lead->owner,
        ];
    }

    /**
     * Extract related model data for the lead.
     *
     * @param  Lead $lead
     *
     * @return array
     */
    private function relationshipData(Lead $lead): array
    {
        return [
            'creator' => $lead->creator,
            'updater' => $lead->updater,
            'deleter' => $lead->deleter,
        ];
    }

    /**
     * Determine authorisation permissions for the lead.
     *
     * @param  Lead $lead
     *
     * @return array
     */
    private function permissionData(Lead $lead): array
    {
        return [
            'permissions' => [
                'view' => Gate::allows('view', $lead),
                'update' => Gate::allows('update', $lead),
                'delete' => Gate::allows('delete', $lead),
            ],
        ];
    }
}
