<?php

namespace App\Services\PartCategories;

use App\Models\PartCategory;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

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

        $paginator = $query->paginate($perPage)->appends($request->query());
 
        $paginator->through(
            fn (PartCategory $partCategory) => $this->formatPartCategory($partCategory)
        );
 
        $paginator->appends([
            'permissions' => [
                'create' => Gate::allows('create', PartCategory::class),
                'viewAny' => Gate::allows('viewAny', PartCategory::class),
            ],
        ]);
 
        return $paginator;
    }

    /**
     * Return a single part category with its parent, children, and parts
     * relationships loaded.
     *
     * @param  PartCategory $partCategory The route-model-bound part category
     * instance.
     *
     * @return array
     */
    public function show(PartCategory $partCategory): array
    {
        $partCategory->load(
            'parent',
            'children',
            'parts',
        );
 
        return $this->formatPartCategory($partCategory);
    }

    /**
     * Format a part category into a structured array.
     *
     * Includes core attributes, related data, and authorisation permissions
     * for the current user.
     *
     * @param  PartCategory $partCategory
     *
     * @return array
     */
    private function formatPartCategory(PartCategory $partCategory): array
    {
        return [
            'id' => $partCategory->id,
            'parent_id' => $partCategory->parent_id,
            'parent' => $partCategory->parent,
            'name' => $partCategory->name,
            'slug' => $partCategory->slug,
            'full_path' => $partCategory->full_path,
            'description' => $partCategory->description,
            'is_test' => $partCategory->is_test,
            'children' => $partCategory->children,
            'parts' => $partCategory->parts,
            'creator' => $partCategory->creator,
            'created_at' => $partCategory->created_at,
            'updated_at' => $partCategory->updated_at,
            'deleted_at' => $partCategory->deleted_at,
            'permissions' => [
                'view' => Gate::allows('view', $partCategory),
                'update' => Gate::allows('update', $partCategory),
                'delete' => Gate::allows('delete', $partCategory),
            ],
        ];
    }
}
