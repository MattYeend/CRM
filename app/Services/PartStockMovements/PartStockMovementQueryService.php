<?php

namespace App\Services\PartStockMovements;

use App\Models\PartStockMovement;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Handles read queries for PartStockMovement records.
 *
 * Delegates sorting to dedicated sub-services and returns
 * paginated serial number results scoped to a given part.
 */
class PartStockMovementQueryService
{
    /**
     * Service responsible for applying sort order to part serial number
     * queries.
     *
     * @var PartStockMovementSortingService
     */
    private PartStockMovementSortingService $sorting;

    /**
     * Inject the required services into the query service.
     *
     * @param  PartStockMovementSortingService $sorting Handles sort order
     * application.
     */
    public function __construct(
        PartStockMovementSortingService $sorting
    ) {
        $this->sorting = $sorting;
    }

    /**
     * Return a paginated list of serial numbers for the given part, with
     * sorting applied.
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
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(1, min((int) $request->query('per_page', 10), 100));

        $query = PartStockMovement::query();

        $this->sorting->applySorting($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single part stock movement with all relevant relationships
     * loaded.
     *
     * @param  PartStockMovement $partStockMovement The route-model-bound
     * part instance.
     *
     * @return PartStockMovement The part stock movement with relationships
     * loaded.
     */
    public function show(
        PartStockMovement $partStockMovement
    ): PartStockMovement {
        return $partStockMovement;
    }
}
