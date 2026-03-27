<?php

namespace App\Services\Suppliers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SupplierQueryService
{
    private SupplierSearchService $search;
    private SupplierSortingService $sorting;
    private SupplierTrashFilterService $trashFilter;
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
     * Return paginated supplier, applying filters/sorting.
     *
     * @param Request $request
     *
     * @return LengthAwarePaginator
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
     * Return a single supplier
     *
     * @param Supplier $supplier
     *
     * @return Supplier
     */
    public function show(Supplier $supplier): Supplier
    {
        return $supplier->load(
            'parts',
            'partSuppliers',
        );
    }
}
