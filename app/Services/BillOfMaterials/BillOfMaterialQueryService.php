<?php

namespace App\Services\BillOfMaterials;

use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BillOfMaterialQueryService
{
    private BillOfMaterialSortingService $sorting;
    private BillOfMaterialTrashFilterService $trashFilter;

    public function __construct(
        BillOfMaterialSortingService $sorting,
        BillOfMaterialTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated BOM entries for a given parent part,
     * applying filters/sorting.
     *
     * @param Part $part
     *
     * @param Request $request
     *
     * @return LengthAwarePaginator
     */
    public function list(Part $part, Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = $part->billOfMaterials()
            ->with('childPart:id,sku,name,quantity,unit_of_measure');

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }
}
