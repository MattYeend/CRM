<?php

namespace App\Services\Suppliers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Handles read queries for Supplier records.
 *
 * Delegates searching, sorting, and trash filtering to dedicated
 * sub-services and returns paginated or single supplier results with
 * the appropriate relationships loaded.
 */
class SupplierQueryService
{
    /**
     * Service responsible for applying the search.
      *
      * @var SupplierSearchService
     */
    private SupplierSearchService $search;

    /**
     * Service responsible for applying sort order.
     *
     * @var SupplierSortingService
     */
    private SupplierSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters.
     *
     * @var SupplierTrashFilterService
     */
    private SupplierTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  SupplierSearchService $search Handles search filtering.
     * @param  SupplierSortingService $sorting Handles sort order.
     * @param  SupplierTrashFilterService $trashFilter Handles
     * trash filtering.
     */
    public function __construct(
        SupplierSearchService $search,
        SupplierSortingService $sorting,
        SupplierTrashFilterService $trashFilter,
    ) {
        $this->search = $search;
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of suppliers with search, sorting,
     * and trash filters applied.
     * 
     * The per_page value is clamped between 1 and 100. All active query
     * string pameters are appended to the paginator links.
     *
     * @param Request $request Incoming HTTP request; may carry search,
     * sorting, and trash filter param.
     *
     * @return LengthAwarePaginator Paginated suppliers item results.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = Supplier::with(
            'parts',
            'partSuppliers',
        );

        $this->search->applySearch($query, $request);
        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single supplier with related date loaded.
     *
     * @param Supplier $supplier The route-model bound supplier
     * instance.
     *
     * @return Supplier The supplier with relations loaded.
     */
    public function show(Supplier $supplier): Supplier
    {
        return $supplier->load(
            'parts',
            'partSuppliers',
        );
    }
}
