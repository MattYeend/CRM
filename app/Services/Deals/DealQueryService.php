<?php

namespace App\Services\Deals;

use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

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
     * Permissions are merged into the top-level response array so the
     * frontend can read them as `data.permissions` without colliding
     * with the paginator's own appends mechanism.
     *
     * @param  Request $request Incoming HTTP request; may carry search,
     * sort, filter, and pagination params.
     *
     * @return array Paginated deal results with top-level permissions key.
     */
    public function list(Request $request): array
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

        $paginator = $query->paginate($perPage)->appends($request->query());

        return $this->transformPaginator($paginator);
    }

    /**
     * Return a single deal with related data loaded.
     *
     * @param  Deal $deal The route-model-bound deal instance.
     *
     * @return array
     */
    public function show(Deal $deal): array
    {
        $deal->load(
            'company',
            'owner',
            'pipeline',
            'stage',
            'notes',
            'tasks',
            'attachments',
            'products',
        );

        return $this->formatDeal($deal);
    }

    /**
     * Convert the paginator to an array and merge top-level permissions.
     *
     * Permissions are added as a root-level key so the Vue frontend can
     * access them as `data.permissions` alongside `data.data`,
     * `data.current_page`, etc.
     *
     * @param  LengthAwarePaginator $paginator The paginator instance
     * containing Deal models.
     *
     * @return array The transformed paginator data with permissions.
     */
    private function transformPaginator(
        LengthAwarePaginator $paginator
    ): array {
        $paginator->through(
            fn (Deal $deal) => $this->formatDeal($deal)
        );

        $result = $paginator->toArray();

        $result['permissions'] = [
            'create' => Gate::allows('create', Deal::class),
            'viewAny' => Gate::allows('viewAny', Deal::class),
        ];

        return $result;
    }

    /**
     * Format a deal into a structured array.
     *
     * Combines core attributes, derived flags, relationships, and permissions.
     *
     * @param  Deal $deal
     *
     * @return array
     */
    private function formatDeal(Deal $deal): array
    {
        return array_merge(
            $this->baseData($deal),
            $this->derivedData($deal),
            $this->relationshipData($deal),
            $this->permissionData($deal),
        );
    }

    /**
     * Extract core deal attributes.
     *
     * @param  Deal $deal
     *
     * @return array
     */
    private function baseData(Deal $deal): array
    {
        return [
            'id' => $deal->id,
            'title' => $deal->title,
            'value' => $deal->value,
            'formatted_value' => $deal->formatted_value,
            'currency' => $deal->currency,
            'status' => $deal->status,
            'close_date' => $deal->close_date,
        ];
    }

    /**
     * Extract derived flags for deal lifecycle.
     *
     * @param  Deal $deal
     *
     * @return array
     */
    private function derivedData(Deal $deal): array
    {
        return [
            'is_open' => $deal->is_open,
            'is_won' => $deal->is_won,
            'is_lost' => $deal->is_lost,
            'is_closed' => $deal->is_closed,
            'is_overdue' => $deal->is_overdue,
        ];
    }

    /**
     * Extract related model data for the deal.
     *
     * Products are included here so they are available in both the Show
     * page and the DealProducts/Index page, which both call fetchDeal()
     * against the API show endpoint.
     *
     * @param  Deal $deal
     *
     * @return array
     */
    private function relationshipData(Deal $deal): array
    {
        return [
            'attachments' => $deal->attachments,
            'company' => $deal->company,
            'notes' => $deal->notes,
            'owner' => $deal->owner,
            'pipeline' => $deal->pipeline,
            'products' => $deal->products,
            'stage' => $deal->stage,
            'tasks' => $deal->tasks,
            'creator' => $deal->creator,
        ];
    }

    /**
     * Determine authorisation permissions for the deal.
     *
     * @param  Deal $deal
     *
     * @return array
     */
    private function permissionData(Deal $deal): array
    {
        return [
            'permissions' => [
                'view' => Gate::allows('view', $deal),
                'update' => Gate::allows('update', $deal),
                'delete' => Gate::allows('delete', $deal),
            ],
        ];
    }
}
