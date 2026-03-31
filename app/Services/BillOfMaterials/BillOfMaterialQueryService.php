<?php

namespace App\Services\BillOfMaterials;

use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Handles read queries for Bill of Materials (BOM) records.
 *
 * Delegates sorting and trash filtering to dedicated sub-services and
 * returns paginated BOM results with the appropriate relationships loaded.
 */
class BillOfMaterialQueryService
{
    /**
     * Service responsible for applying sort order to BOM queries.
     *
     * @var BillOfMaterialSortingService
     */
    private BillOfMaterialSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters to BOM queries.
     *
     * @var BillOfMaterialTrashFilterService
     */
    private BillOfMaterialTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  BillOfMaterialSortingService $sorting Handles sort order
     * application.
     * @param  BillOfMaterialTrashFilterService $trashFilter Handles trash
     * visibility filtering.
     */
    public function __construct(
        BillOfMaterialSortingService $sorting,
        BillOfMaterialTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of BOM entries for a given parent part with
     * sorting and trash filters applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Part $part The parent part whose BOM entries are being queried.
     * @param  Request $request Incoming HTTP request; may carry sort, filter,
     * and pagination params.
     *
     * @return LengthAwarePaginator Paginated BOM results.
     */
    public function list(Part $part, Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = $part->billOfMaterials()
            ->with('childPart:id,sku,name,quantity,unit_of_measure')
            ->getQuery();

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }
}
