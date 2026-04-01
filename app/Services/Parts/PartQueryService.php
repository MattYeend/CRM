<?php

namespace App\Services\Parts;

use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Handles read queries for Part records.
 *
 * Delegates sorting and trash filtering to dedicated sub-services and
 * returns paginated or single part results with all relevant relationships
 * loaded.
 */
class PartQueryService
{
    /**
     * Service responsible for applying sort order to part queries.
     *
     * @var PartSortingService
     */
    private PartSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters to part
     * queries.
     *
     * @var PartTrashFilterService
     */
    private PartTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  PartSortingService $sorting Handles sort order application.
     * @param  PartTrashFilterService $trashFilter Handles trash visibility
     * filtering.
     */
    public function __construct(
        PartSortingService $sorting,
        PartTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of parts with sorting and trash filters applied.
     *
     * Each result eager-loads the product, category, primary supplier, primary
     * image, stock movements, serial numbers, bill of materials, and assembly
     * usage relationships. The per_page value is clamped between 1 and 100.
     * All active query string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry sort, filter,
     * and pagination params.
     *
     * @return LengthAwarePaginator Paginated part results.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = Part::with(
            'product',
            'category',
            'primarySupplier',
            'primaryImage',
            'stockMovements',
            'serialNumbers',
            'billOfMaterials',
            'usedInAssemblies'
        );

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single part with all relevant relationships loaded.
     *
     * @param  Part $part The route-model-bound part instance.
     *
     * @return Part The part with relationships loaded.
     */
    public function show(Part $part): Part
    {
        return $part->load(
            'product',
            'category',
            'primarySupplier',
            'primaryImage',
            'stockMovements',
            'serialNumbers',
            'billOfMaterials',
            'usedInAssemblies'
        );
    }
}
