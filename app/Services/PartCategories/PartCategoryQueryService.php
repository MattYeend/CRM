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

        return $this->transformPaginator($paginator);
    }

    /**
     * Return a single part category with its parent, children, and parts
     * relationships loaded.
     *
     * @param  PartCategory $category The route-model-bound part category
     * instance.
     *
     * @return array
     */
    public function show(PartCategory $category): array
    {
        $category->load(
            'parent',
            'children',
            'parts',
        );

        return $this->formatPartCategory($category);
    }

    /**
     * Apply transformation and append permissions to the paginator.
     *
     * Each part category item is formatted into a structured array and
     * top-level permissions are appended to the paginator response.
     *
     * @param  LengthAwarePaginator $paginator The paginator instance
     * containing PartCategory models.
     *
     * @return LengthAwarePaginator The transformed paginator instance.
     */
    private function transformPaginator(
        LengthAwarePaginator $paginator
    ): LengthAwarePaginator {
        $paginator->through(
            fn (PartCategory $category) => $this->formatPartCategory($category)
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
     * Format a part category into a structured array.
     *
     * Includes core attributes, related data, and authorisation permissions
     * for the current user.
     *
     * @param  PartCategory $category
     *
     * @return array
     */
    private function formatPartCategory(PartCategory $category): array
    {
        return [
            'id' => $category->id,
            'parent_id' => $category->parent_id,
            'parent' => $category->parent,
            'name' => $category->name,
            'slug' => $category->slug,
            'full_path' => $category->full_path,
            'description' => $category->description,
            'children' => $category->children,
            'parts' => $category->parts,
            'creator' => $category->creator,
            'permissions' => [
                'view' => Gate::allows('view', $category),
                'update' => Gate::allows('update', $category),
                'delete' => Gate::allows('delete', $category),
            ],
        ];
    }
}
