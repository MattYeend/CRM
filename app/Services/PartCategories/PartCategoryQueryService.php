<?php

namespace App\Services\PartCategories;

use App\Models\PartCategory;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Handles read queries for PartCategory records.
 *
 * Delegates sorting and trash filtering to dedicated sub-services and
 * returns paginated or single part category results with the parent,
 * children, and parts relationships loaded.
 */
class PartCategoryQueryService
{
    /**
     * Service responsible for applying sort order to part category queries.
     *
     * @var PartCategorySortingService
     */
    private PartCategorySortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters to part
     * category queries.
     *
     * @var PartCategoryTrashFilterService
     */
    private PartCategoryTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  PartCategorySortingService $sorting Handles sort order
     * application.
     * @param  PartCategoryTrashFilterService $trashFilter Handles trash
     * visibility filtering.
     */
    public function __construct(
        PartCategorySortingService $sorting,
        PartCategoryTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of part categories with sorting and trash
     * filters applied.
     *
     * Each result eager-loads the parent, children, and parts relationships.
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry sort, filter,
     * and pagination params.
     *
     * @return LengthAwarePaginator Paginated part category results.
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
     * Return a single part category with its parent, children, and parts
     * relationships loaded.
     *
     * @param  PartCategory $partCategory The route-model-bound part category
     * instance.
     *
     * @return PartCategory The part category with relationships loaded.
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
