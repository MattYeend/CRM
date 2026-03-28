<?php

namespace App\Services\PartSerialNumbers;

use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PartSerialNumberQueryService
{
    private PartSerialNumberSearchService $search;
    private PartSerialNumberSortingService $sorting;
    private PartSerialNumberTrashFilterService $trashFilter;
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
     * Return paginated part serial number, applying filters/sorting.
     *
     * @param Request $request
     *
     * @param Part $part
     *
     * @return LengthAwarePaginator
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
