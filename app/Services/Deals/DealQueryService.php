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

        $paginator = $query->paginate($perPage)->appends($request->query());

        $paginator->through(
            fn (Deal $deal) => $this->formatDeal($deal)
        );

        $paginator->appends([
            'permissions' => [
                'create' => Gate::allows('create', Deal::class),
                'viewAny' => Gate::allows('viewAny', Deal::class),
            ],
        ]);

        return $paginator;
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
        );

        return $this->formatDeal($deal);
    }

    /**
     * Format a deal into a structured array.
     *
     * Includes core attributes, related data, derived accessors,
     * and authorisation permissions for the current user.
     *
     * @param  Deal  $deal
     *
     * @return array
     */
    private function formatDeal(Deal $deal): array
    {
        return [
            'id' => $deal->id,
            'title' => $deal->title,
            'value' => $deal->value,
            'formatted_value' => $deal->formatted_value,
            'currency' => $deal->currency,
            'status' => $deal->status,
            'is_open' => $deal->is_open,
            'is_won' => $deal->is_won,
            'is_lost' => $deal->is_lost,
            'is_closed' => $deal->is_closed,
            'is_overdue' => $deal->is_overdue,
            'close_date' => $deal->close_date,
            'company' => $deal->company,
            'owner' => $deal->owner,
            'pipeline' => $deal->pipeline,
            'stage' => $deal->stage,
            'creator' => $deal->creator,
            'permissions' => [
                'view' => Gate::allows('view', $deal),
                'update' => Gate::allows('update', $deal),
                'delete' => Gate::allows('delete', $deal),
            ],
        ];
    }
}
