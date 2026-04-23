<?php

namespace App\Services\PartCategories;

use App\Models\PartCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry sort, filter,
     * and pagination params.
     *
     * @return array
     */
    public function list(Request $request): array
    {
        $query = PartCategory::with(
            'parent',
            'children',
            'parts',
        );

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $this->paginate($query, $request);

        return array_merge(
            $paginator,
            ['permissions' => $this->getPermissions()]
        );
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
     * Paginate and transform the part category query.
     *
     * @param Builder $query
     * @param Request $request
     *
     * @return array
     */
    private function paginate($query, Request $request): array
    {
        $perPage = max(1, min((int) $request->query('per_page', 10), 100));

        return $query->paginate($perPage)
            ->appends($request->query())
            ->through(fn (
                PartCategory $category
            ): array => $this->formatPartCategory($category))
            ->toArray();
    }

    /**
     * Get permission flags for the current user.
     *
     * @return array
     */
    private function getPermissions(): array
    {
        return [
            'create' => Gate::allows('create', PartCategory::class),
            'viewAny' => Gate::allows('viewAny', PartCategory::class),
        ];
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
