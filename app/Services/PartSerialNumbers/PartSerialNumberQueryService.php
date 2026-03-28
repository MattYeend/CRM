<?php

namespace App\Services\PartSerialNumbers;

use App\Models\PartSerialNumber;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PartSerialNumberQueryService
{
    private PartSerialNumberSortingService $sorting;
    private PartSerialNumberTrashFilterService $trashFilter;
    public function __construct(
        PartSerialNumberSortingService $sorting,
        PartSerialNumberTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated part serial number, applying filters/sorting.
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

        $query = PartSerialNumber::with(
            'part',
        );

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single part
     *
     * @param PartSerialNumber $partSerialNumber
     *
     * @return PartSerialNumber
     */
    public function show(
        PartSerialNumber $partSerialNumber
    ): PartSerialNumber {
        return $partSerialNumber->load(
            'part',
        );
    }
}
