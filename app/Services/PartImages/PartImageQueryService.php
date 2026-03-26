<?php

namespace App\Services\PartImages;

use App\Models\PartImage;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PartImageQueryService
{
    private PartImageSortingService $sorting;
    private PartImageTrashFilterService $trashFilter;
    public function __construct(
        PartImageSortingService $sorting,
        PartImageTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated part image, applying filters/sorting.
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

        $query = PartImage::with(
            'part',
        );

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single part
     *
     * @param PartImage $partImage
     *
     * @return PartImage
     */
    public function show(PartImage $partImage): PartImage
    {
        return $partImage->load(
            'part',
        );
    }
}
