<?php

namespace App\Services\Parts;

use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PartQueryService
{
    private PartSortingService $sorting;
    private PartTrashFilterService $trashFilter;
    public function __construct(
        PartSortingService $sorting,
        PartTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated part, applying filters/sorting.
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

        $query = Part::with(
            'product',
            'category',
            'primarySupplier',
            'primaryImage',
            'stockMovements',
        );

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single part
     *
     * @param Part $part
     *
     * @return Part
     */
    public function show(Part $part): Part
    {
        return $part->load(
            'product',
            'category',
            'primarySupplier',
            'primaryImage',
        );
    }
}
