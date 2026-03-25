<?php

namespace App\Services\PartCategories;

use App\Models\PartCategory;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PartCategoryQueryService
{
    private PartCategorySortingService $sorting;
    private PartCategoryTrashFilterService $trashFilter;
    public function __construct(
        PartCategorySortingService $sorting,
        PartCategoryTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated part category, applying filters/sorting.
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

        $query = PartCategory::with(
            'parent',
            'children',
            'parts',
        );

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single part category
     *
     * @param PartCategory $partCategory
     *
     * @return PartCategory
     */
    public function show(PartCategory $partCategory): PartCategory
    {
        return $partCategory->load(
            'parent',
            'children',
            'parts',
        );
    }
}
