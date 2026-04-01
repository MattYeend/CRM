<?php

namespace App\Services\PartSerialNumbers;

use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Handles read queries for PartSerialNumber records.
 *
 * Delegates search, sorting, and trash filtering to dedicated sub-services
 * and returns paginated serial number results scoped to a given part.
 */
class PartSerialNumberQueryService
{
    /**
     * Service responsible for applying search filters to part serial number
     * queries.
     *
     * @var PartSerialNumberSearchService
     */
    private PartSerialNumberSearchService $search;

    /**
     * Service responsible for applying sort order to part serial number
     * queries.
     *
     * @var PartSerialNumberSortingService
     */
    private PartSerialNumberSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters to part serial
     * number queries.
     *
     * @var PartSerialNumberTrashFilterService
     */
    private PartSerialNumberTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  PartSerialNumberSearchService $search Handles search filter
     * application.
     * @param  PartSerialNumberSortingService $sorting Handles sort order
     * application.
     * @param  PartSerialNumberTrashFilterService $trashFilter Handles trash
     * visibility filtering.
     */
    public function __construct(
        PartSerialNumberSearchService $search,
        PartSerialNumberSortingService $sorting,
        PartSerialNumberTrashFilterService $trashFilter,
    ) {
        $this->search = $search;
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of serial numbers for the given part, with
     * search, sorting, and trash filters applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry search, sort,
     * filter, and pagination params.
     * @param  Part $part The part whose serial numbers should be listed.
     *
     * @return LengthAwarePaginator Paginated part serial number results.
     */
    public function list(Request $request, Part $part): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = $part->serialNumbers()->with('part')->getQuery();

        $this->search->applySearch($query, $request);
        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }
}
